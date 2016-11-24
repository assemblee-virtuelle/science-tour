<?php

namespace TheScienceTour\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use TheScienceTour\UserBundle\Document\UserRole;

class ProfileController extends ContainerAware {

  public function chatAction($id) {
    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
      throw new AccessDeniedException('This user does not have access to this section.');
    }

    $chat = $this->container->get('doctrine_mongodb')
      ->getRepository('TheScienceTourMessageBundle:Chat')
      ->find($id);

    if (!$chat) {
      throw new AccessDeniedException('Aucune conversation trouvée avec l\'id ' . $id);
    }

    if (!$chat->getUsers()->contains($user)) {
      throw new AccessDeniedException('Vous ne participez pas à cette conversation');
    }

    if ($user) {
      $dm = $this->container->get('doctrine_mongodb')->getManager();
      $user->removeNotification("chat", $chat->getId());
      $dm->persist($user);
      $dm->flush();
    }

    return $this->container->get('templating')
      ->renderResponse('TheScienceTourUserBundle:Profile:chat.html.twig', array(
        'user' => $user,
        'chat' => $chat
      ));
  }

  public function showAction($tab) {
    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
      throw new AccessDeniedException('This user does not have access to this section.');
    }

    $dm = $this->container->get('doctrine_mongodb')->getManager();

    $chatRepo = $dm->getRepository('TheScienceTourMessageBundle:Chat');
    $myChats  = $chatRepo->findChatWithUser($user->getId())->execute();

    $myChatsId = array();
    foreach ($myChats as $myChat) {
      $myChatsId[] = $myChat->getId();
    }
    $user->removeUselessNotifications("chat", $myChatsId);

    $projectRepo     = $dm->getRepository('TheScienceTourProjectBundle:Project');
    $createdProjects = $projectRepo->findProjectsCreatedBy($user->getId())
      ->execute();
    //$contribProjects = $projectRepo->findProjectsWithContributor($user->getId())->execute();
    $userskills = $dm->getRepository('TheScienceTourProjectBundle:Skill')
      ->findSkillsWithHelper($user->getId())
      ->execute();
    $idskills   = array();
    foreach ($userskills as $skill) {
      $idskills[] = $skill->getId();
    }
    $contribProjects = $projectRepo->findProjectsWithContributorOrSkills($user->getId(), $idskills)
      ->execute();

    $supportedProjects = $projectRepo->findProjectsWithSupporter($user->getId())
      ->execute();
    $followedProjects  = $projectRepo->findProjectsWithSubscriber($user->getId())
      ->execute();
    $sponsoredProjects = NULL;
    if ($this->container->get('security.context')
      ->isGranted('ROLE_RESEARCHER')
    ) {
      $sponsoredProjects = $projectRepo->findProjectsWithSponsor($user->getId())
        ->execute();
    }
    elseif ($tab == "mypublicinfo") {
      $tab = "myprojects";
    }

    $myProjectsId = array();
    foreach ($createdProjects as $myProject) {
      $myProjectsId[] = $myProject->getId();
    }
    foreach ($contribProjects as $myProject) {
      $myProjectsId[] = $myProject->getId();
    }
    foreach ($supportedProjects as $myProject) {
      $myProjectsId[] = $myProject->getId();
    }
    foreach ($followedProjects as $myProject) {
      $myProjectsId[] = $myProject->getId();
    }
    $user->removeUselessNotifications("project", $myProjectsId);
    $dm->persist($user);
    $dm->flush();

    $drafts = $projectRepo->findDraftsCreatedBy($user->getId())->execute();

    $createdChallenges = array();
    $otherChallenges   = array();
    if ($this->container->get('security.context')
      ->isGranted('ROLE_SUPER_ANIM')
    ) {
      $challenges = $dm->getRepository('TheScienceTourChallengeBundle:Challenge')
        ->findAll();
      foreach ($challenges as $challenge) {
        if ($user == $challenge->getCreator()) {
          $createdChallenges[] = $challenge;
        }
        else {
          $otherChallenges[] = $challenge;
        }
      }
    }
    elseif ($tab == "mychallenges") {
      $tab = "myprojects";
    }

    return $this->container->get('templating')
      ->renderResponse('FOSUserBundle:Profile:show.html.' . $this->container->getParameter('fos_user.template.engine'),
        array(
          'user'              => $user,
          'myChats'           => $myChats,
          'createdProjects'   => $createdProjects,
          'contribProjects'   => $contribProjects,
          'supportedProjects' => $supportedProjects,
          'followedProjects'  => $followedProjects,
          'sponsoredProjects' => $sponsoredProjects,
          'drafts'            => $drafts,
          'createdChallenges' => $createdChallenges,
          'otherChallenges'   => $otherChallenges,
          'tab'               => $tab
        ));
  }

  public function editAction() {
    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
      throw new AccessDeniedException('This user does not have access to this section.');
    }

    $form        = $this->container->get('fos_user.profile.form');
    $formHandler = $this->container->get('fos_user.profile.form.handler');

    $process = $formHandler->process($user);
    if ($process) {
      $this->setFlash('fos_user_success', 'profile.flash.updated');

      return new RedirectResponse($this->getRedirectionUrl($user));
    }

    return $this->container->get('templating')->renderResponse(
      'FOSUserBundle:Profile:edit.html.' . $this->container->getParameter('fos_user.template.engine'),
      array('form' => $form->createView())
    );
  }

  public function editPublicInfoAction() {
    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
      throw new AccessDeniedException('This user does not have access to this section.');
    }

    if (!$this->container->get('security.context')
      ->isGranted('ROLE_RESEARCHER')
    ) {
      throw new AccessDeniedException('Tu n\'es pas chercheur !');
    }

    $form = $this->container->get('form.factory')->createBuilder('form', $user)
      ->add('info1', 'purified_textarea')
      ->add('info2', 'purified_textarea')
      ->add('info3', 'purified_textarea')
      ->getForm();

    $request = $this->container->get('request');
    if ($request->getMethod() == "POST") {
      $form->bind($request);
      if ($form->isValid()) {
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $dm->persist($user);
        $dm->flush();

        return new RedirectResponse($this->container->get('router')
          ->generate('fos_user_profile_show', array('tab' => 'mypublicinfo')));
      }
    }

    return $this->container->get('templating')
      ->renderResponse('TheScienceTourUserBundle:Profile:editpublicinfo.html.twig',
        array('form' => $form->createView())
      );
  }

  protected function getRedirectionUrl(UserInterface $user) {
    return $this->container->get('router')->generate('fos_user_profile_show');
  }

  protected function setFlash($action, $value) {
    $this->container->get('session')->getFlashBag()->set($action, $value);
  }

  public function publicProfileAction($nickname) {
    $other = $this->container->get('fos_user.user_manager')
      ->findUserByUsername($nickname);
    if (!$other || !$other->hasRole('ROLE_RESEARCHER')) {
      throw new NotFoundHttpException('Cet utilisateur n\'a pas été trouvé.');
    }

    $dm          = $this->container->get('doctrine_mongodb')->getManager();
    $projectRepo = $dm->getRepository('TheScienceTourProjectBundle:Project');
    $projects    = $projectRepo->findProjectsWithSponsor($other->getId())
      ->execute();

    $challenges = array();
    foreach ($projects as $project) {
      if ($project->getChallenge() && !in_array($project->getChallenge(), $challenges)) {
        $challenges[] = $project->getChallenge();
      }
    }


    return $this->container->get('templating')
      ->renderResponse('TheScienceTourUserBundle:Profile:publicprofile.html.twig', array(
        'other'      => $other,
        'projects'   => $projects,
        'challenges' => $challenges
      ));
  }

  public function addRoleAction() {

    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
      throw new AccessDeniedException('This user does not have access to this section.');
    }

    if (!$this->container->get('security.context')
      ->isGranted('ROLE_RESEARCHER')
    ) {
      throw new AccessDeniedException('Tu n\'es pas chercheur !');
    }

    $picRepo     = $this->container->get('doctrine_mongodb')
      ->getRepository('TheScienceTourMediaBundle:Media');
    $pictureList = $picRepo->findByContext('researcher_role');


    $userRole = new UserRole();

    $form = $this->container->get('form.factory')
      ->createBuilder('form', $userRole)
      ->add('organization', 'text')
      ->add('job', 'text', array('required' => FALSE))
      ->add('picture', 'sonata_media_type', array(
        'provider' => 'sonata.media.provider.image',
        'context'  => 'researcher_role',
        'required' => FALSE
      ))
      ->getForm();

    $request = $this->container->get('request');
    if ($request->getMethod() == "POST") {
      $form->bind($request);
      if ($form->isValid()) {
        $picId = $request->request->get('pic_id');
        if ($picId) {
          $picture = $picRepo->find($picId);
          if (!$picture || $picture->getContext() != "researcher_role") {
            $userRole->setPicture(NULL);
          }
          else {
            $userRole->setPicture($picture);
          }
        }
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $user->addUserRole($userRole);
        $dm->persist($user);
        $dm->flush();

        return new RedirectResponse($this->container->get('router')
          ->generate('fos_user_profile_show', array('tab' => 'mypublicinfo')));
      }
    }

    return $this->container->get('templating')
      ->renderResponse('TheScienceTourUserBundle:Profile:adduserrole.html.twig',
        array('form' => $form->createView(), 'pictureList' => $pictureList)
      );
  }

  public function deleteRoleAction($idrole) {

    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
      throw new AccessDeniedException('This user does not have access to this section.');
    }

    if (!$this->container->get('security.context')
      ->isGranted('ROLE_RESEARCHER')
    ) {
      throw new AccessDeniedException('Tu n\'es pas chercheur !');
    }

    $idrole = intval($idrole);
    $role   = $user->getUserRoles()->get($idrole);

    $dm = $this->container->get('doctrine_mongodb')->getManager();
    $user->removeUserRole($role);
    $dm->flush();

    return new RedirectResponse($this->container->get('router')
      ->generate('fos_user_profile_show', array('tab' => 'mypublicinfo')));

  }
}
