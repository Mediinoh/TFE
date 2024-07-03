<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ChatMessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/chat', name: 'chat.index', methods: ['GET', 'POST'])]  
    public function index(MessageRepository $messageRepository, Request $request, EntityManagerInterface $manager): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $form = $this->createForm(ChatMessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->isBloque()) {
                $this->addFlash('danger', 'Vous ne pouvez pas ajouter de message dans le chat car vous êtes bloqué !');
            } else {
                $message = $form->getData();
                $message->setUtilisateur($user);
                
                $manager->persist($message);
                $manager->flush();

                $this->addFlash('success', 'Votre message a été ajouté !');
            }

            return $this->redirectToRoute('chat.index');
        }

        $messages = $messageRepository->recupererMessages();

        return $this->render('pages/chat/list.html.twig', [
            'messages' => $messages,
            'form' => $form->createView(),
        ]);
    }
}
