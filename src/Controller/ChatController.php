<?php

namespace App\Controller;

// Importation des classes nécessaires
use App\Entity\Message;
use App\Entity\MessageReaction;
use App\Entity\Utilisateur;
use App\Form\ChatMessageType;
use App\Form\MessageReactionType;
use App\Form\ReplyMessageType;
use App\Repository\MessageReactionRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChatController extends AbstractController
{
    // Route principale pour l'affichage du chat et l'ajout de messages
    #[Route('/chat', name: 'chat.index', methods: ['GET', 'POST'])]
    public function index(MessageRepository $messageRepository, Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale(); // Détermine la langue pour la traduction des messages
        
        /** @var Utilisateur $user */
        $user = $this->getUser();

        // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Création du formulaire de message
        $form = $this->createForm(ChatMessageType::class);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si l'utilisateur est bloqué
            if ($user->isBloque()) {
                $this->addFlash('danger', $translator->trans('chat_message_blocked', [], 'messages', $locale));
            } else {
                $message = $form->getData(); // Récupère le message
                $message->setUtilisateur($user);

                // Sauvegarde le message
                $manager->persist($message);
                $manager->flush();

                $this->addFlash('success', $translator->trans('message_added', [], 'messages', $locale));
            }

            // Redirige pour éviter la soumission multiple
            return $this->redirectToRoute('chat.index');
        }

        $replyTo = $request->query->get('replyTo'); // Identifiant du message auquel répondre
        
        // Gestion des réponses aux messages
        if (!is_null($replyTo)) {
            $replyForm = $this->createForm(ReplyMessageType::class);
            $replyForm->handleRequest($request);

            if ($replyForm->isSubmitted() && $replyForm->isValid()) {
                $formData = $replyForm->getData();
                $replyMessage = new Message();
                $replyMessage->setMessage($formData['message']);
                $replyMessage->setUtilisateur($user);

                $parentMessage = $messageRepository->find($replyTo);

                if ($parentMessage) {
                    $replyMessage->setReponseA($parentMessage);

                    $manager->persist($replyMessage);
                    $manager->flush();

                    $this->addFlash('success', $translator->trans('reply_added', [], 'messages', $locale));
                }

                return $this->redirectToRoute('chat.index');
            }
        }

        // Récupération de tous les messages du chat
        $messagesData = $messageRepository->recupererMessages();

        $replyMessageForms = [];
        $formLikes = [];
        $formDislikes = [];

        foreach ($messagesData as $message) {
            $messageId = $message[0]->getId();

            // Formulaire pour répondre à un message
            $replyMessageForm = $this->createForm(ReplyMessageType::class, null, [
                'action' => $this->generateUrl('chat.index', ['replyTo' => $messageId]),
                'method' => 'POST',
            ]);

            $replyMessageForms[$messageId] = $replyMessageForm->createView();
            
            // Formulaire pour ajouter un "like"
            $formLike = $this->createForm(MessageReactionType::class, null, [
                'action' => $this->generateUrl('chat.reaction', ['id' => $messageId, 'type' => 'like']),
                'method' => 'POST',
                'reactionCount' => $message['likeCount'],
            ]);
            $formLikes[$messageId] = $formLike->createView();
            
            // Formulaire pour ajouter un "dislike"
            $formDislike = $this->createForm(MessageReactionType::class, null, [
                'action' => $this->generateUrl('chat.reaction', ['id' => $messageId, 'type' => 'dislike']),
                'method' => 'POST',
                'reaction' => 'dislike',
                'reactionCount' => $message['dislikeCount'],
            ]);
            $formDislikes[$messageId] = $formDislike->createView();
        }

        // Retourne la vue avec les messages et les différents formulaires (message, réponse et réaction)
        return $this->render('pages/chat/list.html.twig', [
            'messagesData' => $messagesData,
            'form' => $form->createView(),
            'replyMessageForms' => $replyMessageForms,
            'formLikes' => $formLikes,
            'formDislikes' => $formDislikes,
        ]);
    }

    // Route pour gérer les réactions (like/dislike) sur les messages
    #[Route('/chat/reaction/{id}/{type}', 'chat.reaction', methods: ['POST'])]
    public function chatReaction(int $id, string $type, MessageRepository $messageRepository, MessageReactionRepository $messageReactionRepository, EntityManagerInterface $manager): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        // Redirection vers la page de connexion si l'utilisateur n'existe pas
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $message = $messageRepository->find($id);

        // Si le message n'existe pas, redirection vers la page du chat
        if (!$message) {
            return $this->redirectToRoute('chat.index');
        }

        $messageReaction = $messageReactionRepository->findOneBy([
            'message' => $message,
            'utilisateur' => $user,
        ]);

        // Si l'utilisateur a déjà réagi au message
        if ($messageReaction) {
            // Si même type de réaction, la retire
            if ($messageReaction->getReactionType() === $type) {
                $manager->remove($messageReaction);
            // Sinon, change la réaction
            } else {
                $messageReaction->setReactionType($type);
                $manager->persist($messageReaction);
            }
        // Si aucune réaction précédente, en ajoute une
        } else {
            $reaction = new MessageReaction();
            $reaction->setMessage($message)
                     ->setUtilisateur($user)
                     ->setReactionType($type);
            $manager->persist($reaction);
        }
        $manager->flush();

        return $this->redirectToRoute('chat.index');
    }
}
