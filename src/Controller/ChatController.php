<?php

namespace App\Controller;

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
    #[Route('/chat', name: 'chat.index', methods: ['GET', 'POST'])]
    public function index(MessageRepository $messageRepository, Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale();
        
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $form = $this->createForm(ChatMessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->isBloque()) {
                $this->addFlash('danger', $translator->trans('chat_message_blocked', [], 'messages', $locale));
            } else {
                $message = $form->getData();
                $message->setUtilisateur($user);

                $manager->persist($message);
                $manager->flush();

                $this->addFlash('success', $translator->trans('message_added', [], 'messages', $locale));
            }

            return $this->redirectToRoute('chat.index');
        }

        $replyTo = $request->query->get('replyTo');
        
        // Gestion des réponses
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

        $messagesData = $messageRepository->recupererMessages();

        $replyMessageForms = [];
        $formLikes = [];
        $formDislikes = [];

        foreach ($messagesData as $message) {
            $messageId = $message[0]->getId();

            $replyMessageForm = $this->createForm(ReplyMessageType::class, null, [
                'action' => $this->generateUrl('chat.index', ['replyTo' => $messageId]),
                'method' => 'POST',
            ]);

            $replyMessageForms[$messageId] = $replyMessageForm->createView();
            
            $formLike = $this->createForm(MessageReactionType::class, null, [
                'action' => $this->generateUrl('chat.reaction', ['id' => $messageId, 'type' => 'like']),
                'method' => 'POST',
                'reactionCount' => $message['likeCount'],
            ]);
            $formLikes[$messageId] = $formLike->createView();
            
            $formDislike = $this->createForm(MessageReactionType::class, null, [
                'action' => $this->generateUrl('chat.reaction', ['id' => $messageId, 'type' => 'dislike']),
                'method' => 'POST',
                'reaction' => 'dislike',
                'reactionCount' => $message['dislikeCount'],
            ]);
            $formDislikes[$messageId] = $formDislike->createView();
        }

        return $this->render('pages/chat/list.html.twig', [
            'messagesData' => $messagesData,
            'form' => $form->createView(),
            'replyMessageForms' => $replyMessageForms,
            'formLikes' => $formLikes,
            'formDislikes' => $formDislikes,
        ]);
    }

    #[Route('/chat/reaction/{id}/{type}', 'chat.reaction', methods: ['POST'])]
    public function chatReaction(int $id, string $type, MessageRepository $messageRepository, MessageReactionRepository $messageReactionRepository, EntityManagerInterface $manager): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $message = $messageRepository->find($id);

        if (!$message) {
            return $this->redirectToRoute('chat.index');
        }

        $messageReaction = $messageReactionRepository->findOneBy([
            'message' => $message,
            'utilisateur' => $user,
        ]);

        if ($messageReaction) {
            if ($messageReaction->getReactionType() === $type) {
                $manager->remove($messageReaction);
            } else {
                $messageReaction->setReactionType($type);
                $manager->persist($messageReaction);
            }
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
