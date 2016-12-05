<?php

namespace TheScienceTour\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TheScienceTour\ProjectBundle\Document\Project;
use TheScienceTour\ProjectBundle\Document\News;
use TheScienceTour\ProjectBundle\Document\Help;
use TheScienceTour\ProjectBundle\Form\ResourceType;
use TheScienceTour\ProjectBundle\Form\SkillType;
use TheScienceTour\MessageBundle\Document\Chat;
use TheScienceTour\MessageBundle\Document\Message;
use Ivory\GoogleMap\Places\AutocompleteType;
use TheScienceTour\MainBundle\Model\GeoNear;
use TheScienceTour\ProjectBundle\Document\ProjectTranslation;


class ProjectTranslationController extends Controller {

  public function panelsAction($projectList, $nbByRow, $mgr) {
    return $this->render('TheScienceTourProjectBundle::projectPanels.html.twig', array(
      'projectList' => $projectList,
      'nbByRow'     => $nbByRow,
      'mgr'         => $mgr

    ));
  }

  public function draftPanelsAction($projectList, $search = FALSE) {
    return $this->render('TheScienceTourProjectBundle::draftPanels.html.twig', array(
      'projectList' => $projectList,
      'search'      => $search
    ));
  }

  public function projectsAction($filter, $center) {
    if ($form = $this->getRequest()->query->get('form', FALSE)) {
      if ($form['center']) {
        return $this->redirect($this->generateUrl('tst_projects', array(
          'filter' => $filter,
          'center' => $form['center']
        )));
      }
    }

    // + Get Document Manager and repositories
    // + --------------------------------------------------
    $dm          = $this->get('doctrine_mongodb')->getManager();
    $projectRepo = $dm->getRepository('TheScienceTourProjectBundle:Project');

    //$user = $this->getUser();
    // + --------------------------------------------------

    // + Geocoders
    // + --------------------------------------------------
    $mapHelper = $this->get('the_science_tour_map.map_helper');

    // + Fetch projects
    // + --------------------------------------------------
    $geoNear           = NULL;
    $centerCoordinates = array();
    $maxDistance       = 50; // km
    // For local testing put in your public IP.
    $userGeocode = $mapHelper->getGeocode($_SERVER['REMOTE_ADDR']);

    if ($center == 'around-me') {
      $geoNear           = new GeoNear($userGeocode->getLatitude(), $userGeocode->getLongitude(), $maxDistance);
      $centerCoordinates = array(
        'latitude'  => $userGeocode->getLatitude(),
        'longitude' => $userGeocode->getLongitude()
      );
    }
    elseif ($center && $center != 'all') {
      $geocode           = $mapHelper->getGeocode($center);
      $geoNear           = new GeoNear($geocode->getLatitude(), $geocode->getLongitude(), $maxDistance);
      $centerCoordinates = array(
        'latitude'  => $geocode->getLatitude(),
        'longitude' => $geocode->getLongitude()
      );
    }

    $session   = $this->get('session');
    $isErasmus = $session->get('isErasmus', FALSE);

    // In progress
    $inProgressProjectsQuery = $projectRepo->findInProgress($geoNear, $isErasmus);
    $inProgressProjects      = $inProgressProjectsQuery->execute();
    // Youngest projects
    $youngestProjectsQuery = $projectRepo->findLastUpdated($geoNear, $isErasmus);
    $youngestProjects      = $youngestProjectsQuery->execute();
    // Finished
    $finishedProjectsQuery = $projectRepo->findFinished($geoNear, $isErasmus);
    $finishedProjects      = $finishedProjectsQuery->execute();
    // Finished soon
    $finishedSoonProjectsQuery = $projectRepo->findFinishedSoon($geoNear, $isErasmus);
    $finishedSoonProjects      = $finishedSoonProjectsQuery->execute();

    $route           = array(
      'routeName'  => 'tst_projects',
      'parameters' => array(
        'filter' => $filter,
        'center' => $center
      )
    );
    $aroundMeGeoNear = new GeoNear($userGeocode->getLatitude(), $userGeocode->getLongitude(), $maxDistance);

    // Map menus
    $menus = array(
      array(
        'title'  => $this->get('translator')->trans('Projects'),
        'before' => array(
          'name'       => 'TheScienceTourMapBundle:MapFilterGeoNear:default',
          'params'     => array('route' => $route),
          'controller' => TRUE
        ),
        'items'  => array(
          array(
            'href'    => $this->generateUrl(
              'tst_projects',
              array('filter' => 'youngest', 'center' => $center)
            ),
            'active'  => ($filter == 'youngest'),
            'icon'    => 'icon-rocket',
            'text'    => $this->get('translator')->trans('The youngest'),
            'details' => $youngestProjects->count()
          ),
          array(
            'href'    => $this->generateUrl(
              'tst_projects',
              array('filter' => 'in-progress', 'center' => $center)
            ),
            'active'  => ($filter == 'in-progress'),
            'icon'    => 'icon-refresh',
            'text'    => $this->get('translator')->trans('In progress'),
            'details' => $inProgressProjects->count()
          ),
          array(
            'href'    => $this->generateUrl(
              'tst_projects',
              array('filter' => 'finished-soon', 'center' => $center)
            ),
            'active'  => ($filter == 'finished-soon'),
            'icon'    => 'icon-time',
            'text'    => $this->get('translator')->trans('Finished soon'),
            'details' => $finishedSoonProjects->count()
          ),
          array(
            'href'    => $this->generateUrl(
              'tst_projects',
              array('filter' => 'finished', 'center' => $center)
            ),
            'active'  => ($filter == 'finished'),
            'icon'    => 'icon-flag',
            'text'    => $this->get('translator')->trans('Finished'),
            'details' => $finishedProjects->count()
          )
        )
      )
    );

    switch ($filter) {
      case 'in-progress':
        $listTitle        = 'In progress';
        $projectListQuery = $inProgressProjectsQuery;
        $projectList      = $inProgressProjects;
        $mapProjectList   = $projectRepo->findInProgress(NULL, $isErasmus)
          ->execute();
        break;

      case 'finished-soon':
        $listTitle        = 'Finished soon';
        $projectListQuery = $finishedSoonProjectsQuery;
        $projectList      = $finishedSoonProjects;
        $mapProjectList   = $projectRepo->findFinishedSoon(NULL, $isErasmus)
          ->execute();
        break;

      case 'finished':
        $listTitle        = 'Finished';
        $projectListQuery = $finishedProjectsQuery;
        $projectList      = $finishedProjects;
        $mapProjectList   = $projectRepo->findFinished(NULL, $isErasmus)
          ->execute();
        break;

      default:
        $listTitle        = 'The youngest';
        $projectListQuery = $youngestProjectsQuery;
        $projectList      = $youngestProjects;
        $mapProjectList   = $projectRepo->findLastUpdated(NULL, $isErasmus)
          ->execute();

    }

    $paginator  = $this->get('knp_paginator');
    $pagination = $paginator->paginate(
      $projectListQuery,
      $this->get('request')->query->get('page', 1),
      12
    );

    return $this->render('TheScienceTourProjectBundle::projects.html.twig', array(
      'mapProjectList'    => $mapProjectList,
      'projectList'       => $projectList,
      'pagination'        => $pagination,
      'listTitle'         => $listTitle,
      'route'             => $route,
      'menus'             => $menus,
      'centerCoordinates' => $centerCoordinates
    ));
  }

  public function projectAction($id, $tab) {
    $user = $this->getUser();

    $project = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Project')
      ->find($id);
    if (!$project || $project->getStatus() == 0) {
      throw $this->createNotFoundException('Aucun projet publié trouvé avec l\'id ' . $id);
    }

    if ($user) {
      $dm = $this->get('doctrine_mongodb')->getManager();
      if ($tab == "resources") {
        $user->removeNotification("project-resources", $project->getId());
      }
      if ($tab == "news") {
        $user->removeNotification("project-news", $project->getId());
      }
      if ($tab == "chats") {
        $user->removeNotification("project-chats", $project->getId());
        /*
    			foreach ($project->getChats() as $chat) {
    				$user->removeNotification("chat", $chat->getId());
    				foreach ($chat->getMessages() as $message) {
    					if ($message->getUnreadBy()->contains($user)) {
    						$message->removeUnreadBy($user);
    					}
    				}
    			}
    			*/
      }
      $dm->persist($user);
      $dm->flush();
    }

    $newsList = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:News')
      ->findOrderByDate($id);

    $isEditable = ($user == $project->getCreator() || $this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD'));

    $geocode = $this->get('the_science_tour_map.map_helper')
      ->getGeocode($project->getPlace());
    $city    = $geocode->getCity();
    $city    = $city ? $city : $project->getPlace();

    $teamMember = $project->getTeam()->contains($user);

    $allContribArray = array();
    foreach ($project->getSkills() as $skill) {
      foreach ($skill->getHelpers() as $helper) {
        if (array_key_exists($helper->getUsernameCanonical(), $allContribArray)) {
          $allContribArray[$helper->getUsernameCanonical()][1] .= " / " . $skill->getName();
        }
        else {
          $allContribArray[$helper->getUsernameCanonical()] = array(
            $helper,
            $skill->getName()
          );
        }
      }
    }
    foreach ($project->getContributors() as $contrib) {
      if (!array_key_exists($contrib->getUsernameCanonical(), $allContribArray)) {
        $allContribArray[$contrib->getUsernameCanonical()] = array(
          $contrib,
          ''
        );
      }
    }
    ksort($allContribArray);

    return $this->render('TheScienceTourProjectBundle::project.html.twig', array(
      'project'         => $project,
      'newsList'        => $newsList,
      'isEditable'      => $isEditable,
      'tab'             => $tab,
      'city'            => $city,
      "teamMember"      => $teamMember,
      "allContribArray" => $allContribArray
    ));
  }

  private function _formProjectTranslation($translation, $edit = FALSE) {
    // Get erasmus site status.
    $session   = $this->get('session');
    $isErasmus = $session->get('isErasmus', false);

    $form = $this->createFormBuilder($translation, array('cascade_validation' => true))
                 ->add('title', 'text')
                 ->add('original', 'hidden')
                 ->add('language', 'hidden')
                 ->add('goal', 'purified_textarea')
                 ->add('description', 'purified_textarea');


    if ($edit) {
      $lang         = $this->container->getParameter('erasmusLanguages');
      $lang['none'] = '-- ' . $this->get('translator')
          ->trans('Translate to...') . ' --';

      $form->add('language', 'choice', [
        'choices'           => $lang,
        'preferred_choices' => ['none'],
        'multiple'          => FALSE,
        'expanded'          => FALSE
      ]);

    }

    // $form->add('picture', 'sonata_media_type', array(
    //   'provider' => 'sonata.media.provider.image',
    //   'context'  => 'project',
    //   'required' => $edit && $project->getPicture()
    // ));

    if (!$edit || $translation->getStatus() == 0) {
      $form->add('draft', 'submit', array(
        'attr' => [
            'formnovalidate' => 'formnovalidate',
            'class'          => 'button white_button'
        ]))
           ->add('publish', 'submit', [
               'attr'              => ['class' => 'button orange_button'],
               'validation_groups' => ['Default', 'publish']
           ]);
    }
    else {
      $form->add('save', 'submit', array(
        'attr'              => ['class' => 'button orange_button'],
        'validation_groups' => ['Default', 'publish']
      ));
    }

    return $form;
  }


  /**
   * addProjectTranslationAction Affichage du formulaire pour l'ajout d'une traduction de projet
   *
   * @param Int $original clef primaire du document original à traduire
   * @param String $language langue cible de la traduction
   * @return Response formulaire de saisie de la traduction
   */
  public function addProjectTranslationAction($original, $language) {
    $user = $this->getUser();
    if (!$user) {
    //  throw new AccessDeniedException();
    }
    // Get erasmus site status.
    $session   = $this->get('session');
    $isErasmus = $session->get('isErasmus', FALSE);

    $project = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Project')
      ->find($original);

    $translated = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:ProjectTranslation')
      ->findOneBy(['original' => $project->getId(), 'language' => $language]);

    if (empty($translated)) {
        $translated = new ProjectTranslation();
        $translated->setOriginal($project->getId());
        $translated->setLanguage($language);
        $translated->setTitle($project->getTitle());
        $translated->setGoal($project->getGoal());
        $translated->setDescription($project->getDescription());

        $form = $this->_formProjectTranslation($translated);

        // Build form.
        $form    = $form->getForm();
        $request = $this->get('request');


        return $this->render('TheScienceTourProjectBundle:translations:form.html.twig', array(
            'message'    => $this->get('translator')->trans('You are about to translate the project : '),
            'isErasmus'  => $isErasmus,
            'form'       => $form->createView(),
            'language'   => $language,
            'original'   => $project,
            'translated' => $translated,
            'isEditForm' => false,
            'isAddForm'  => true
        ));
    } else {
        return $this->editProjectTranslationAction($translated->getId());
    }


  }

  /**
   * persistProjectTranslationAction Traitement du formulaire pour l'ajout d'une traduction à un projet
   *
   * @param Request $request Données du formulaire
   * @return Response formulaire de saisie de la traduction
   */
  public function persistProjectTranslationAction(Request $request)
  {
    if ($request->getMethod() == 'POST') {
        $user = $this->getUser();
        if (!$user) {
            // throw new AccessDeniedException();
        }
        // Get erasmus site status.
        $session   = $this->get('session');
        $isErasmus = $session->get('isErasmus', false);
        $translation = new ProjectTranslation();
        // Build form.
        $form = $this->_formProjectTranslation($translation)->getForm();
        $form->bind($request);

        if ($form->isValid()) {
            $project = $this->get('doctrine_mongodb')
            ->getRepository('TheScienceTourProjectBundle:Project')
            ->find($translation->getOriginal());

            // TODO: Affectation de la paternité de la traduction (validation)
            $translation->setTranslator($user);
            $translation->setUpdatedAt(new \DateTime);
            $translation->setOriginal($project);

            // var_dump($translation->getCreatedAt()); die;

            if ($form->get('draft')->isClicked()) {
                $translation->setStatus(0);
            }
            else {
                $translation->setStatus(1);
                $translation->setPublishedAt(new \Datetime);
            }
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($translation);
            $dm->flush();
            if ($form->get('draft')->isClicked()) {
                return $this->redirect($this->generateUrl('fos_user_profile_show', array('tab' => "mydrafts")));
            }

            return $this->redirect($this->generateUrl('tst_project', array('id' => $project->getId())));
      }
    }
  }

  /**
   * editProjectTranslationAction Modification de la traduction d'un projet
   * @param  integer $id clef primaire de la traduction
   * @return Response Formulaire d'édition de la traduction
   */
  public function editProjectTranslationAction($id)
  {
    $user = $this->getUser();
    if (!$user) {
      throw new AccessDeniedException();
    }
    // Get erasmus site status.
    $session   = $this->get('session');
    $isErasmus = $session->get('isErasmus', FALSE);

    $project = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:ProjectTranslation')
      ->find($id);

    if (!$project) {
      throw $this->createNotFoundException('Aucun projet trouvé avec l\'id ' . $id);
    }
    if ($user != $project->getCreator() && !($this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD'))
    ) {
      throw new AccessDeniedException();
    }

    $form = $this->_formProjectAction($project, true)->getForm();

    $request = $this->get('request');
    /*
    if ($request->getMethod() == 'POST') {
      $form->bind($request);
      if ($form->isValid()) {
        if (!$project->getPicture()->getSize()) {
          $project->setPicture(NULL);
        }
        if (!$project->getChallenge()) {
          $project->eraseSponsors();
        }
        $project->updateFinishedAt();
        $dm = $this->get('doctrine_mongodb')->getManager();

        if ($project->getStatus() == 0) {
          if ($form->get('publish')->isClicked()) {
            $project->setStatus(1);
            $project->setPublishedAt(new \Datetime);
            $news = new News();
            $news->setProjectId($project->getId());
            $news->setAuthor($user);
            $news->setTitle("{% publishProject %}");
            $dm->persist($news);
          }
        }
        else {
          $project->setStatus(1);
        }

        // If the place has change then clear the coordinates
        // so they are updated in MapBundle\Listener\CoordinatesSetterSubscriber
        $original = $dm->getUnitOfWork()->getOriginalDocumentData($project);
        if ($original['place'] != $project->getPlace()) {
          $project->unsetCoordinates();
        }

        $dm->persist($project);

        $challenge = $project->getChallenge();
        if ($challenge and $challenge != $originalChallenge) {
          $challengeCreator = $challenge->getCreator();
          $challengeCreator->addNotification("challenge-newproject", $challenge->getId());
          $dm->persist($challengeCreator);
        }

        $dm->flush();
        if ($project->getStatus() == 0) {
          return $this->redirect($this->generateUrl('fos_user_profile_show', array('tab' => "mydrafts")));
        }
        return $this->redirect($this->generateUrl('tst_project', array('id' => $project->getId())));
      }
    }
    */

    return $this->render('TheScienceTourProjectBundle:translations:editTranslation.html.twig', array(
      'message'    => '',
      'project'    => $project,
      'form'       => $form->createView(),
      'isErasmus'  => $isErasmus,
      'isEditForm' => true,
      'isAddForm'  => false
    ));
  }

  public function adminProjectAction($id, $tab) {
    $user = $this->getUser();
    if (!$user) {
      throw new AccessDeniedException();
    }
    $project = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Project')
      ->find($id);

    if (!$project || $project->getStatus() == 0) {
      throw $this->createNotFoundException('Aucun projet publié trouvé avec l\'id ' . $id);
    }
    if ($user != $project->getCreator() && !($this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD'))
    ) {
      throw new AccessDeniedException();
    }

    $request = $this->get('request');

    if ($tab == "team") {

      if ($request->getMethod() == 'POST') {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $contributors       = $project->getContributors();
        $deleteContributors = $request->request->get('deleteContributors');

        $toDel = array();
        for ($i = 0; $i < count($contributors); $i++) {
          if ($deleteContributors[$i] == "1") {
            $toDel[] = $contributors[$i];

            $from = $this->container->getParameter('mailer_sender_address');

            $message = \Swift_Message::newInstance()
              ->setSubject('The Science Tour : Annulation de participation au projet ' . $project->getTitle())
              ->setFrom($from)
              ->setTo($contributors[$i]->getEmail())
              ->setBody($this->renderView('TheScienceTourProjectBundle:contributors:deleteContributorMail.txt.twig', array(
                'user'        => $user,
                'project'     => $project,
                'contributor' => $contributors[$i]
              )));

            $this->get('mailer')->send($message);
          }
        }

        foreach ($toDel as $contributor) {
          $project->removeContributor($contributor);
        }


        $skills        = $project->getSkills();
        $deleteHelpers = $request->request->get('deleteHelpers');

        for ($i = 0; $i < count($skills); $i++) {
          $helpers = $skills[$i]->getHelpers();
          $toDel   = array();
          for ($j = 0; $j < count($helpers); $j++) {
            if ($deleteHelpers[$i][$j] == "1") {
              $toDel[] = $helpers[$j];

              if ($helpers[$j] != $project->getCreator()) {

                $from = $this->container->getParameter('mailer_sender_address');

                $message = \Swift_Message::newInstance()
                  ->setSubject('The Science Tour : Annulation d\'aide au projet ' . $project->getTitle())
                  ->setFrom($from)
                  ->setTo($helpers[$j]->getEmail())
                  ->setBody($this->renderView('TheScienceTourProjectBundle:skillHelpers:deleteSkillHelperMail.txt.twig', array(
                    'user'    => $user,
                    'project' => $project,
                    'skill'   => $skills[$i],
                    'helper'  => $helpers[$j]
                  )));

                $this->get('mailer')->send($message);
              }
            }
          }

          foreach ($toDel as $helper) {
            $skills[$i]->removeHelper($helper);
          }

        }


        $dm->flush();

        //return $this->redirect($this->generateUrl('tst_project', array('id' => $project->getId())));
      }


      return $this->render('TheScienceTourProjectBundle:admin:adminTeam.html.twig', array(
        'project' => $project
      ));

    }
    else {

      if ($request->getMethod() == 'POST') {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $tools         = $project->getTools();
        $receivedTools = $request->request->get('receivedTools');
        $neededTools   = $request->request->get('neededTools');
        $deleteTools   = $request->request->get('deleteTools');

        for ($i = 0; $i < count($tools); $i++) {
          $helps = $tools[$i]->getUncompletedHelps();
          $toDel = array();
          for ($j = 0; $j < count($helps); $j++) {
            if ($deleteTools[$i][$j] == "1") {
              $toDel[] = $helps[$j];

              $from = $this->container->getParameter('mailer_sender_address');

              $message = \Swift_Message::newInstance()
                ->setSubject('The Science Tour : Annulation d\'aide au projet ' . $project->getTitle())
                ->setFrom($from)
                ->setTo($helps[$j]->getHelper()->getEmail())
                ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resDeleteHelpMail.txt.twig', array(
                  'project' => $project,
                  'res'     => $tools[$i],
                  'help'    => $helps[$j]
                )));

              $this->get('mailer')->send($message);

            }
            elseif (intval($receivedTools[$i][$j]) != 0) {
              $helps[$j]->setNbNeeded(intval($receivedTools[$i][$j]));
              $helps[$j]->setNbReceived(intval($receivedTools[$i][$j]));
              if ($helps[$j]->getChat()) {
                foreach ($helps[$j]->getChat()->getUsers() as $chatUser) {
                  $chatUser->removeNotification("chat", $helps[$j]->getChat()
                    ->getId());
                  $dm->persist($chatUser);
                }
                $dm->remove($helps[$j]->getChat());
                $helps[$j]->setChat(NULL);
              }

              $from = $this->container->getParameter('mailer_sender_address');

              $message = \Swift_Message::newInstance()
                ->setSubject('The Science Tour : Don reçu pour le projet ' . $project->getTitle())
                ->setFrom($from)
                ->setTo($helps[$j]->getHelper()->getEmail())
                ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resCompletedHelpMail.txt.twig', array(
                  'project' => $project,
                  'res'     => $tools[$i],
                  'help'    => $helps[$j]
                )));

              $this->get('mailer')->send($message);

              $news = new News();
              $news->setProjectId($project->getId());
              $news->setAuthor($helps[$j]->getHelper());
              $news->setTitle("{% giveTool %}");
              $news->setContent($tools[$i]->getName() . '{%#%}' . intval($receivedTools[$i][$j]));
              $dm->persist($news);
              foreach ($project->getEverybody() as $member) {
                if ($member != $user) {
                  $member->addNotification("project-news", $project->getId());
                  $dm->persist($member);
                }
              }

            }
            elseif (intval($neededTools[$i][$j]) != 0) {
              if ($helps[$j]->getNbNeeded() != intval($neededTools[$i][$j])) {
                $helps[$j]->setNbNeeded(intval($neededTools[$i][$j]));
                if ($helps[$j]->getChat() == NULL) {
                  $chat = new Chat();
                  $chat->addUser($user);
                  $chat->addUser($helps[$j]->getHelper());
                  $chat->setTitle($project->getTitle() . ' - ' . $helps[$j]->getNbNeeded() . ' x ' . $tools[$i]->getName());
                  $dm->persist($chat);
                  $helps[$j]->setChat($chat);
                  $dm->flush();
                }

                $message = new Message();
                $message->addUnreadBy($helps[$j]->getHelper());
                if ($helps[$j]->getNbProposed() == $helps[$j]->getNbNeeded()) {
                  $message->setContent("Message automatique : Votre proposition de don de " . $helps[$j]->getNbNeeded() . " " . $tools[$i]->getName() . " a été acceptée.");
                }
                else {
                  $message->setContent("Message automatique : Votre proposition de don a été acceptée. Il n'y aura cependant besoin que de " . $helps[$j]->getNbNeeded() . " " . $tools[$i]->getName() . ", au lieu des " . $helps[$j]->getNbProposed() . " proposés.");
                }
                $helps[$j]->getChat()->addMessage($message);

                $from = $this->container->getParameter('mailer_sender_address');

                $message = \Swift_Message::newInstance()
                  ->setSubject('The Science Tour : Nouveau message')
                  ->setFrom($from)
                  ->setTo($helps[$j]->getHelper()->getEmail())
                  ->setBody($this->renderView('TheScienceTourMessageBundle::newMessageMail.txt.twig', array(
                    'chat'   => $helps[$j]->getChat(),
                    'author' => NULL
                  )));

                $helps[$j]->getHelper()
                  ->addNotification("chat", $helps[$j]->getChat()->getId());
                $dm->persist($helps[$j]->getHelper());
                $dm->flush();

                $this->get('mailer')->send($message);

              }
            }
            else {
              $helps[$j]->setNbNeeded(0);
            }
          }
          foreach ($toDel as $help) {
            if ($help->getChat()) {
              foreach ($help->getChat()->getUsers() as $chatUser) {
                $chatUser->removeNotification("chat", $help->getChat()
                  ->getId());
                $dm->persist($chatUser);
              }
              $dm->remove($help->getChat());
            }
            $tools[$i]->removeHelp($help);
            $dm->persist($tools[$i]);
            $dm->flush();
          }
        }

        $materials         = $project->getMaterials();
        $receivedMaterials = $request->request->get('receivedMaterials');
        $neededMaterials   = $request->request->get('neededMaterials');
        $deleteMaterials   = $request->request->get('deleteMaterials');

        for ($i = 0; $i < count($materials); $i++) {
          $helps = $materials[$i]->getUncompletedHelps();
          $toDel = array();
          for ($j = 0; $j < count($helps); $j++) {
            if ($deleteMaterials[$i][$j] == "1") {
              $toDel[] = $helps[$j];

              $from = $this->container->getParameter('mailer_sender_address');

              $message = \Swift_Message::newInstance()
                ->setSubject('The Science Tour : Annulation d\'aide au projet ' . $project->getTitle())
                ->setFrom($from)
                ->setTo($helps[$j]->getHelper()->getEmail())
                ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resDeleteHelpMail.txt.twig', array(
                  'project' => $project,
                  'res'     => $materials[$i],
                  'help'    => $helps[$j]
                )));

              $this->get('mailer')->send($message);

            }
            elseif (intval($receivedMaterials[$i][$j]) != 0) {
              $helps[$j]->setNbNeeded(intval($receivedMaterials[$i][$j]));
              $helps[$j]->setNbReceived(intval($receivedMaterials[$i][$j]));
              if ($helps[$j]->getChat()) {
                foreach ($helps[$j]->getChat()->getUsers() as $chatUser) {
                  $chatUser->removeNotification("chat", $helps[$j]->getChat()
                    ->getId());
                  $dm->persist($chatUser);
                }
                $dm->remove($helps[$j]->getChat());
                $helps[$j]->setChat(NULL);
              }

              $from = $this->container->getParameter('mailer_sender_address');

              $message = \Swift_Message::newInstance()
                ->setSubject('The Science Tour : Don reçu pour le projet ' . $project->getTitle())
                ->setFrom($from)
                ->setTo($helps[$j]->getHelper()->getEmail())
                ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resCompletedHelpMail.txt.twig', array(
                  'project' => $project,
                  'res'     => $materials[$i],
                  'help'    => $helps[$j]
                )));

              $this->get('mailer')->send($message);

              $news = new News();
              $news->setProjectId($project->getId());
              $news->setAuthor($helps[$j]->getHelper());
              $news->setTitle("{% giveMaterial %}");
              $news->setContent($materials[$i]->getName() . '{%#%}' . intval($receivedMaterials[$i][$j]));
              $dm->persist($news);
              foreach ($project->getEverybody() as $member) {
                if ($member != $user) {
                  $member->addNotification("project-news", $project->getId());
                  $dm->persist($member);
                }
              }

            }
            elseif (intval($neededMaterials[$i][$j]) != 0) {
              if ($helps[$j]->getNbNeeded() != intval($neededMaterials[$i][$j])) {
                $helps[$j]->setNbNeeded(intval($neededMaterials[$i][$j]));
                if ($helps[$j]->getChat() == NULL) {
                  $chat = new Chat();
                  $chat->addUser($user);
                  $chat->addUser($helps[$j]->getHelper());
                  $chat->setTitle($project->getTitle() . ' - ' . $helps[$j]->getNbNeeded() . ' x ' . $materials[$i]->getName());
                  $dm->persist($chat);
                  $helps[$j]->setChat($chat);
                  $dm->flush();
                }

                $message = new Message();
                $message->addUnreadBy($helps[$j]->getHelper());
                if ($helps[$j]->getNbProposed() == $helps[$j]->getNbNeeded()) {
                  $message->setContent("Message automatique : Votre proposition de don de " . $helps[$j]->getNbNeeded() . " " . $materials[$i]->getName() . " a été acceptée.");
                }
                else {
                  $message->setContent("Message automatique : Votre proposition de don a été acceptée. Il n'y aura cependant besoin que de " . $helps[$j]->getNbNeeded() . " " . $materials[$i]->getName() . ", au lieu des " . $helps[$j]->getNbProposed() . " proposés.");
                }
                $helps[$j]->getChat()->addMessage($message);

                $from = $this->container->getParameter('mailer_sender_address');

                $message = \Swift_Message::newInstance()
                  ->setSubject('The Science Tour : Nouveau message')
                  ->setFrom($from)
                  ->setTo($helps[$j]->getHelper()->getEmail())
                  ->setBody($this->renderView('TheScienceTourMessageBundle::newMessageMail.txt.twig', array(
                    'chat'   => $helps[$j]->getChat(),
                    'author' => NULL
                  )));

                $helps[$j]->getHelper()
                  ->addNotification("chat", $helps[$j]->getChat()->getId());
                $dm->persist($helps[$j]->getHelper());
                $dm->flush();

                $this->get('mailer')->send($message);

              }
            }
            else {
              $helps[$j]->setNbNeeded(0);
            }
          }
          foreach ($toDel as $help) {
            if ($help->getChat()) {
              foreach ($help->getChat()->getUsers() as $chatUser) {
                $chatUser->removeNotification("chat", $help->getChat()
                  ->getId());
                $dm->persist($chatUser);
              }
              $dm->remove($help->getChat());
            }
            $materials[$i]->removeHelp($help);
            $dm->persist($materials[$i]);
            $dm->flush();
          }
        }

        $premises         = $project->getPremises();
        $receivedPremises = $request->request->get('receivedPremises');
        $neededPremises   = $request->request->get('neededPremises');
        $deletePremises   = $request->request->get('deletePremises');

        for ($i = 0; $i < count($premises); $i++) {
          $helps = $premises[$i]->getUncompletedHelps();
          $toDel = array();
          for ($j = 0; $j < count($helps); $j++) {
            if ($deletePremises[$i][$j] == "1") {
              $toDel[] = $helps[$j];

              $from = $this->container->getParameter('mailer_sender_address');

              $message = \Swift_Message::newInstance()
                ->setSubject('The Science Tour : Annulation d\'aide au projet ' . $project->getTitle())
                ->setFrom($from)
                ->setTo($helps[$j]->getHelper()->getEmail())
                ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resDeleteHelpMail.txt.twig', array(
                  'project' => $project,
                  'res'     => $premises[$i],
                  'help'    => $helps[$j]
                )));

              $this->get('mailer')->send($message);

            }
            elseif (intval($receivedPremises[$i][$j]) != 0) {
              $helps[$j]->setNbNeeded(intval($receivedPremises[$i][$j]));
              $helps[$j]->setNbReceived(intval($receivedPremises[$i][$j]));
              if ($helps[$j]->getChat()) {
                foreach ($helps[$j]->getChat()->getUsers() as $chatUser) {
                  $chatUser->removeNotification("chat", $helps[$j]->getChat()
                    ->getId());
                  $dm->persist($chatUser);
                }
                $dm->remove($helps[$j]->getChat());
                $helps[$j]->setChat(NULL);
              }

              $from = $this->container->getParameter('mailer_sender_address');

              $message = \Swift_Message::newInstance()
                ->setSubject('The Science Tour : Don reçu pour le projet ' . $project->getTitle())
                ->setFrom($from)
                ->setTo($helps[$j]->getHelper()->getEmail())
                ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resCompletedHelpMail.txt.twig', array(
                  'project' => $project,
                  'res'     => $premises[$i],
                  'help'    => $helps[$j]
                )));

              $this->get('mailer')->send($message);

              $news = new News();
              $news->setProjectId($project->getId());
              $news->setAuthor($helps[$j]->getHelper());
              $news->setTitle("{% givePremise %}");
              $news->setContent($premises[$i]->getName() . '{%#%}' . intval($receivedPremises[$i][$j]));
              $dm->persist($news);
              foreach ($project->getEverybody() as $member) {
                if ($member != $user) {
                  $member->addNotification("project-news", $project->getId());
                  $dm->persist($member);
                }
              }

            }
            elseif (intval($neededPremises[$i][$j]) != 0) {
              if ($helps[$j]->getNbNeeded() != intval($neededPremises[$i][$j])) {
                $helps[$j]->setNbNeeded(intval($neededPremises[$i][$j]));
                if ($helps[$j]->getChat() == NULL) {
                  $chat = new Chat();
                  $chat->addUser($user);
                  $chat->addUser($helps[$j]->getHelper());
                  $chat->setTitle($project->getTitle() . ' - ' . $helps[$j]->getNbNeeded() . ' x ' . $premises[$i]->getName());
                  $dm->persist($chat);
                  $helps[$j]->setChat($chat);
                  $dm->flush();
                }

                $message = new Message();
                $message->addUnreadBy($helps[$j]->getHelper());
                if ($helps[$j]->getNbProposed() == $helps[$j]->getNbNeeded()) {
                  $message->setContent("Message automatique : Votre proposition de don de " . $helps[$j]->getNbNeeded() . " " . $premises[$i]->getName() . " a été acceptée.");
                }
                else {
                  $message->setContent("Message automatique : Votre proposition de don a été acceptée. Il n'y aura cependant besoin que de " . $helps[$j]->getNbNeeded() . " " . $premises[$i]->getName() . ", au lieu des " . $helps[$j]->getNbProposed() . " proposés.");
                }
                $helps[$j]->getChat()->addMessage($message);

                $from = $this->container->getParameter('mailer_sender_address');

                $message = \Swift_Message::newInstance()
                  ->setSubject('The Science Tour : Nouveau message')
                  ->setFrom($from)
                  ->setTo($helps[$j]->getHelper()->getEmail())
                  ->setBody($this->renderView('TheScienceTourMessageBundle::newMessageMail.txt.twig', array(
                    'chat'   => $helps[$j]->getChat(),
                    'author' => NULL
                  )));

                $helps[$j]->getHelper()
                  ->addNotification("chat", $helps[$j]->getChat()->getId());
                $dm->persist($helps[$j]->getHelper());
                $dm->flush();

                $this->get('mailer')->send($message);

              }
            }
            else {
              $helps[$j]->setNbNeeded(0);
            }
          }
          foreach ($toDel as $help) {
            if ($help->getChat()) {
              foreach ($help->getChat()->getUsers() as $chatUser) {
                $chatUser->removeNotification("chat", $help->getChat()
                  ->getId());
                $dm->persist($chatUser);
              }
              $dm->remove($help->getChat());
            }
            $premises[$i]->removeHelp($help);
            $dm->persist($premises[$i]);
            $dm->flush();
          }
        }

        $project->setSupporters();
        $dm->flush();

        return $this->redirect($this->generateUrl('tst_project', array(
          'id'  => $project->getId(),
          'tab' => "resources"
        )));
      }

      return $this->render('TheScienceTourProjectBundle:admin:adminProposals.html.twig', array(
        'project' => $project

      ));
    }

  }
}
