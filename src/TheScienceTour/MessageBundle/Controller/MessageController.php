<?php

namespace TheScienceTour\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TheScienceTour\MessageBundle\Document\Message;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MessageController extends Controller {
	
	public function chatAction($chat, $request) {
		if (!$chat) {
			throw $this->createNotFoundException('erreur');
		}
		$user = $this->getUser();
		if (!$user) {
			throw new AccessDeniedException();
		}
		if (!$chat->getUsers()->contains($user)) {
			throw $this->createNotFoundException('Vous ne participez pas à cette conversation');
		}
		
		$dm = $this->get('doctrine_mongodb')->getManager();
		
		if ($request->getMethod() == 'POST') {
			$mess = $request->request->get('message');
			if ($mess != null && $mess != "") {
				
				$message = new Message();
				$message->setAuthor($user);
				$message->setContent($mess);
				$chat->addMessage($message);

				if (!($chat->getUsers()->count() == 1 && $chat->getUsers()->first() == $user)) {
					$from = $this->container->getParameter('mailer_sender_address');
					$mail = \Swift_Message::newInstance()
						->setSubject('The Science Tour : Nouveau message')
						->setFrom($from)
						->setTo($from)
						->setBody($this->renderView('TheScienceTourMessageBundle::newMessageMail.txt.twig', array(
							'chat' => $chat,
							'author' => $user
					)));
					foreach ($chat->getUsers() as $chatUser) {
						if ($chatUser != $user) {
							$message->addUnreadBy($chatUser);
							$mail->addBcc($chatUser->getEmail());
							$chatUser->addNotification("chat", $chat->getId());
							$dm->persist($chatUser);
						}
					}				
		
					$this->get('mailer')->send($mail);
				}
			}
		}
		
		$unreadMessages = array();
		foreach ($chat->getMessages() as $message) {
			if ($message->getUnreadBy()->contains($user)) {
				$unreadMessages[] = $message;
				$message->removeUnreadBy($user);
			}
		}
		
		$dm->flush();

		return $this->render('TheScienceTourMessageBundle::chat.html.twig', array(
			'chat' => $chat,
			'unreadMessages' => $unreadMessages
		));
	}

}
