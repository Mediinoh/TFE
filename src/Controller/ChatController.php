<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\ChatMessageType;
use App\Form\ReplyMessageType; // Un formulaire distinct pour les réponses
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
    public function index(
        MessageRepository $messageRepository,
        Request $request,
        EntityManagerInterface $manager,
        TranslatorInterface $translator
    ): Response {
        $locale = $request->getLocale();
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

        $messages = $messageRepository->recupererMessages();
        $replyForms = [];

        foreach ($messages as $message) {
            $replyForm = $this->createForm(ReplyMessageType::class, null, [
                'action' => $this->generateUrl('chat.index', ['replyTo' => $message->getId()]),
            ]);
            $replyForms[$message->getId()] = $replyForm->createView();
        }

        return $this->render('pages/chat/list.html.twig', [
            'messages' => $messages,
            'form' => $form->createView(),
            'replyForms' => $replyForms,
        ]);
    }
}
