<?php

namespace TheScienceTour\ChallengeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TheScienceTour\ChallengeBundle\Document\Challenge;
use TheScienceTour\ChallengeBundle\Form\ChallengeResType;
use TheScienceTour\MessageBundle\Document\Chat;
use TheScienceTour\MessageBundle\Document\Message;


class ChallengeController extends Controller {

	/**
	 * Render challenge panels
	 */

	public function challengePanelsAction($challengeList, $nbByRow, $mgr, $language = NULL) {
		if (is_null($language)) {
			$language = $this->container->getParameter('locale');
		}
		return $this->render('TheScienceTourChallengeBundle::challengePanels.html.twig', array(
			'challengeList' => $challengeList,
			'nbByRow' => $nbByRow,
			'mgr' => $mgr,
			'askedForTranslation' => $language
		));
	}



	/**
	 * challengesAction Show all challenges
	 *
	 * @return Response List of challenges (closed and in progress)
	 */

	public function challengesAction() {
		$dm = $this->get('doctrine_mongodb')->getManager();
		$isErasmus = $this->get('session')->get('isErasmus');
		$defaultLocale = $this->container->getParameter('locale');
		$locale = $defaultLocale;;

		$challengeRepo = $dm->getRepository('TheScienceTourChallengeBundle:Challenge');

		$inProgressChallenges = $challengeRepo->findInProgress($isErasmus, $defaultLocale);

		$pastChallengesQuery = $challengeRepo->findPast($isErasmus, $defaultLocale);
		// foreach($pastChallengesQuery as $past) {
		// 	$availableTranslation = $past->getTranslations()->filter(function ($document) use ($locale) {
		// 		return $document->getLanguage() == $locale;
		// 	});
		// 	$pastChallengesTranslated[] = empty($availableTranslation) ? $past : $availableTranslation;
		// }
		$paginator = $this->get('knp_paginator');
		$pastChallenges = $paginator->paginate(
			$pastChallengesQuery,
			$this->get('request')->query->get('page', 1),
			6
		);

		$inProgressProjects =  new \Doctrine\Common\Collections\ArrayCollection();
		foreach ($inProgressChallenges as $challenge) {
			foreach ($challenge->getProjects() as $project) {
				$inProgressProjects[] = $project;
			}
		}

		return $this->render('TheScienceTourChallengeBundle::challenges.html.twig', array(
			'inProgressChallenges' => $inProgressChallenges,
			'pastChallenges' => $pastChallenges,
			'inProgressProjects' => $inProgressProjects,
			'askedForTranslation' => $locale
		));

	}



	/**
	 * Show one challenge
	 */

	public function challengeAction($id, $tab) {
		$user = $this->getUser();

		$challenge = $this->get('doctrine_mongodb')
			->getRepository('TheScienceTourChallengeBundle:Challenge')
			->find($id);
		if (!$challenge) {
			throw $this->createNotFoundException('Aucun défi trouvé avec l\'id '.$id);
		}

		if ($user) {
			$dm = $this->get('doctrine_mongodb')->getManager();
			if ($tab == "chats") {
				$user->removeNotification("challenge-chats", $challenge->getId());
			}
			$user->removeNotification("challenge-newproject", $challenge->getId());
			$dm->persist($user);
			$dm->flush();
		}

		$isEditable = $this->get('security.context')->isGranted('ROLE_PROJECT_MOD');

		$paginator = $this->get('knp_paginator');
		$projects = $paginator->paginate(
				$challenge->getProjects(),
				$this->get('request')->query->get('page', 1),
				9
		);

		return $this->render('TheScienceTourChallengeBundle::challenge.html.twig', array(
			'challenge' => $challenge,
			'projects' => $projects,
			'isEditable' => $isEditable,
			'tab' => $tab,
		));
	}



	/**
	 * editChallengeAction Add or edit a challenge
	 *
	 * @var integer $id Id of the document to edit (NULL if creation)
	 * @var integer $translated_id Id of the document to be translated (NULL by default)
	 * @return Response The form to be displayed on screen
	 */

	public function editChallengeAction($id) {
		$isErasmus = $this->get('session')->get('isErasmus');
		// $locale = $this->getLocale();

		$newChallenge = ($id == null);
		$user = $this->getUser();
		if (!$user || !$this->get('security.context')->isGranted('ROLE_SUPER_ANIM')) {
			throw new AccessDeniedException();
		}
		if ($newChallenge) {
			$challenge = new Challenge();
			$challenge->setCreator($user);
			$challenge->setStartedAt(new \DateTime("tomorrow"));
			$challenge->setIsErasmus($isErasmus);
			// TODO : Confirmer le choix de la langue pour le Science Tour
			if (!$isErasmus) {
				$challenge->setLanguage($this->getLocale());
			}
		} else {
			$challenge = $this->get('doctrine_mongodb')
				->getRepository('TheScienceTourChallengeBundle:Challenge')
				->find($id);
			if (!$challenge) {
				throw $this->createNotFoundException('Aucun défi trouvé avec l\'id '.$id);
			}
		}
		$form = $this->createFormBuilder($challenge, array('cascade_validation' => true))
			->add('title', 'text')
			->add('startedAt', 'date')
			->add('duration', 'integer', ['attr' => ['min' => 1]])
			->add('durationUnit', 'choice', [
				'choices' => ['day' => 'Jours', 'week' => 'Semaines', 'month' => "Mois"]
			])
			->add('description', 'purified_textarea')
			->add('rules', 'purified_textarea')
			->add('tools', 'collection', array('type' => new ChallengeResType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false))
			->add('materials', 'collection', array('type' => new ChallengeResType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false))
			->add('premises', 'collection', array('type' => new ChallengeResType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false))
			->add('skills', 'collection', array('type' => new ChallengeResType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false));
		if ($isErasmus) {
			$form->add('language', 'choice',
						['choices' => $this->container->getParameter('erasmusLanguages'),
						 'preferred_choices' => [$this->get('request')->getLocale()],
						 'multiple' => false,
						 'expanded' => false]);
		}
		if ($newChallenge) {
			$form->add('picture', 'sonata_media_type', array(
					'provider' => 'sonata.media.provider.image',
					'context'  => 'challenge'
			));
		} else {
			$form->add('picture', 'sonata_media_type', array(
					'provider'	=> 'sonata.media.provider.image',
					'context'	=> 'project',
					'required' => false
			));
		}
		$form = $form->getForm();

		$request = $this->get('request');
		if ($request->getMethod() == 'POST') {
			$challenge->removeRes();
			$form->bind($request);
			if ($form->isValid()) {
				if (!$challenge->getPicture()->getSize()) {
					$challenge->setPicture(null);
				}
				$challenge->updateFinishedAt();
				$dm = $this->get('doctrine_mongodb')->getManager();
				$dm->persist($challenge);
				$dm->flush();
				return $this->redirect($this->generateUrl('tst_challenge', array('id' => $challenge->getId())));
			}
		}

		return $this->render('TheScienceTourChallengeBundle::edit.html.twig', array(
				'form' => $form->createView(),
				'challenge' => $challenge,
				'newChallenge' => $newChallenge
		));
	}



	/**
	 * Subscribe to a challenge
	 */

	public function subscribeAction($id) {

		$user = $this->getUser();
		if (!$user) {
			throw new AccessDeniedException();
		}

		$challenge = $this->get('doctrine_mongodb')
			->getRepository('TheScienceTourChallengeBundle:Challenge')
			->find($id);
		if (!$challenge) {
			throw $this->createNotFoundException('Aucun défi trouvé avec l\'id '.$id);
		}
		if ($challenge->getSubscribers()->contains($user)) {
			throw $this->createNotFoundException('Vous êtes déjà abonné à ce défi !');
		}

		$dm = $this->get('doctrine_mongodb')->getManager();
		$challenge->addSubscriber($user);
		$dm->persist($challenge);
		$dm->flush();

		return $this->redirect($this->generateUrl('tst_challenge', array('id' => $id)));

	}



	/**
	 * Unsubscribe to a challenge
	 */

	public function unsubscribeAction($id) {
		$user = $this->getUser();
		if (!$user) {
			throw new AccessDeniedException();
		}

		$challenge = $this->get('doctrine_mongodb')
			->getRepository('TheScienceTourChallengeBundle:Challenge')
			->find($id);
		if (!$challenge) {
			throw $this->createNotFoundException('Aucun défi trouvé avec l\'id '.$id);
		}
		if (!$challenge->getSubscribers()->contains($user)) {
			throw $this->createNotFoundException('Vous n\'êtes pas abonné à ce défi !');
		}

		$dm = $this->get('doctrine_mongodb')->getManager();
		$challenge->removeSubscriber($user);
		$dm->persist($challenge);
		$dm->flush();

		return $this->redirect($this->generateUrl('tst_challenge', array('id' => $id)));
	}



	/**
	 * Add chat/message
	 */

	public function addChatMessageAction($id, $idchat) {
		$newChat = ($idchat == null);
		$user = $this->getUser();
		if (!$user) {
			throw new AccessDeniedException();
		}
		$challenge = $this->get('doctrine_mongodb')
			->getRepository('TheScienceTourChallengeBundle:Challenge')
			->find($id);

		if (!$challenge) {
			throw $this->createNotFoundException('Aucun défi trouvé avec l\'id '.$id);
		}

		if ($newChat) {
			$chat = new Chat();
		} else {
			$chat = $this->get('doctrine_mongodb')
				->getRepository('TheScienceTourMessageBundle:Chat')
				->find($idchat);
			if (!$chat) {
				throw $this->createNotFoundException('Aucune discussion trouvée avec l\'id '.$idchat);
			}
			if (!$challenge->getChats()->contains($chat)) {
				throw $this->createNotFoundException('Cette discussion ne fait pas partie du défi');
			}
		}

		$error_message = null;
		$mess = "";

		$request = $this->get('request');
		if ($request->getMethod() == 'POST') {
			$mess = $request->request->get('message');

			$html_purifier = $this->container->get('exercise_html_purifier.twig_extension');
			$mess_nohtml = $html_purifier->purify($mess, 'no_html');

			if ($mess == null || $mess_nohtml == "") {
				$error_message = "Tu dois entrer un message !";
			} elseif (strlen($mess_nohtml) > 5000) {
				$error_message = "Tu es trop bavard ! (5000 caractères maximum)";
			} else {
				$dm = $this->get('doctrine_mongodb')->getManager();

				$chat->addUser($user);

				$message = new Message();
				$message->setAuthor($user);
				$message->setContent($mess);

				$chat->addMessage($message);

				if ($newChat) {
					$mess_begin = strlen($mess_nohtml) > 25 ? mb_substr($mess_nohtml, 0, 25, 'utf-8').'...' : $mess_nohtml;
					$chat->setTitle($challenge->getTitle().' - '.$mess_begin);
					$challenge->addChat($chat);

					if ($user != $challenge->getCreator()) {
						$from = $this->container->getParameter('mailer_sender_address');
						$mail = \Swift_Message::newInstance()
							->setSubject('The Science Tour : Nouvelle discussion dans votre défi '.$challenge->getTitle())
							->setFrom($from)
							->setTo($challenge->getCreator()->getEmail())
							->setBody($this->renderView('TheScienceTourChallengeBundle:chat:addChatMail.txt.twig', array(
								'challenge' => $challenge,
								'chat' => $chat,
								'author' => $user
						)));

						$this->get('mailer')->send($mail);
						$challenge->getCreator()->addNotification("challenge-chats", $challenge->getId());
						$dm->persist($challenge->getCreator());
					}
				} else {
					if (!($chat->getUsers()->count() == 1 && $chat->getUsers()->first() == $user)) {
						$from = $this->container->getParameter('mailer_sender_address');
						$mail = \Swift_Message::newInstance()
							->setSubject('The Science Tour : Nouveau message - défi '.$challenge->getTitle())
							->setFrom($from)
							->setTo($from)
							->setBody($this->renderView('TheScienceTourChallengeBundle:chat:addMessageMail.txt.twig', array(
								'challenge' => $challenge,
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

				$challenge->setUpdatedAt(new \Datetime);

				$dm->persist($chat);
				$dm->persist($challenge);
				$dm->flush();

				return $this->redirect($this->generateUrl('tst_challenge', array('id' => $id, 'tab' => "chats")));
			}

		}

		return $this->render('TheScienceTourChallengeBundle:chat:addChatMessage.html.twig', array(
				'challenge' => $challenge,
				'chat' => $chat,
				'newChat' => $newChat,
				'error_message' => $error_message,
				'mess' => $mess
		));

	}



	/**
	 * Delete chat
	 */

	public function deleteChatAction($id, $idchat) {
		$user = $this->getUser();
		if (!$user) {
			throw new AccessDeniedException();
		}
		$challenge = $this->get('doctrine_mongodb')
			->getRepository('TheScienceTourChallengeBundle:Challenge')
			->find($id);

		if (!$challenge) {
			throw $this->createNotFoundException('Aucun défi trouvé avec l\'id '.$id);
		}

		if ($user != $challenge->getCreator() && !($this->get('security.context')->isGranted('ROLE_SUPER_ANIM'))) {
			throw new AccessDeniedException();
		}

		$chat = $this->get('doctrine_mongodb')
			->getRepository('TheScienceTourMessageBundle:Chat')
			->find($idchat);

		if (!$chat) {
			throw $this->createNotFoundException('Aucune discussion trouvée avec l\'id '.$idchat);
		}
		if (!$challenge->getChats()->contains($chat)) {
			throw $this->createNotFoundException('Cette discussion ne fait pas partie du défi');
		}

		$dm = $this->get('doctrine_mongodb')->getManager();
		foreach ($chat->getUsers() as $chatUser) {
			$chatUser->removeNotification("chat", $chat->getId());
			$dm->persist($chatUser);
		}
		$challenge->removeChat($chat);

		if (!($chat->getUsers()->count() == 1 && $chat->getUsers()->first() == $user)) {

			$from = $this->container->getParameter('mailer_sender_address');
			$mail = \Swift_Message::newInstance()
				->setSubject('The Science Tour : Discussion supprimée - défi '.$challenge->getTitle())
				->setFrom($from)
				->setTo($from)
				->setBody($this->renderView('TheScienceTourChallengeBundle:chat:deleteChatMail.txt.twig', array(
					'chat' => $chat,
					'user' => $user
			)));
			foreach ($chat->getUsers() as $chatUser) {
				if ($chatUser != $user) {
					$mail->addBcc($chatUser->getEmail());
				}
			}

			$this->get('mailer')->send($mail);

		}

		$dm->remove($chat);
		$dm->persist($challenge);
		$dm->flush();
		return $this->redirect($this->generateUrl('tst_challenge', array('id' => $challenge->getId(), 'tab' => "chats")));

	}
}
