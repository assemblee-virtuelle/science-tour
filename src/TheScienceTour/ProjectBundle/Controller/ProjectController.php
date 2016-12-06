<?php

namespace TheScienceTour\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Ivory\GoogleMap\Places\AutocompleteType;

use TheScienceTour\ProjectBundle\Document\Project;
use TheScienceTour\ProjectBundle\Document\News;
use TheScienceTour\ProjectBundle\Document\Help;
use TheScienceTour\ProjectBundle\Form\ResourceType;
use TheScienceTour\ProjectBundle\Form\SkillType;
use TheScienceTour\MessageBundle\Document\Chat;
use TheScienceTour\MessageBundle\Document\Message;
use TheScienceTour\MainBundle\Model\GeoNear;
use TheScienceTour\ProjectBundle\Document\ProjectTranslation;
use TheScienceTour\ProjectBundle\Form\TranslationLanguageType;

class ProjectController extends Controller {

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

  private function _formProjectAction($project, $edit = FALSE) {
    // Get erasmus site status.
    $session   = $this->get('session');
    $isErasmus = $session->get('isErasmus', false);

    $form = $this->createFormBuilder($project, array('cascade_validation' => true))
      ->add('title', 'text');

    // if ($edit) {
    //   $lang         = $this->container->getParameter('erasmusLanguages');
    //   $lang['none'] = '-- ' . $this->get('translator')
    //       ->trans('Translate to...') . ' --';
    //
    //   $form->add('translations', 'choice', [
    //     'choices'           => $lang,
    //     'preferred_choices' => ['none'],
    //     'multiple'          => FALSE,
    //     'expanded'          => FALSE
    //   ]);
    // }

    $form->add('place', 'places_autocomplete', array(
      'prefix'   => 'js_tst_place_',
      'types'    => array(AutocompleteType::CITIES),
      'async'    => FALSE,
      'language' => 'fr',
      'attr'     => array(
        'placeholder' => '',
        'oninvalid'   => 'javascript:show(0);',
        'required'    => 'required'
      )
    ));

    // No challenge for Erasmus.
    if (!$isErasmus) {
      $challengeList = $this->get('doctrine_mongodb')
        ->getRepository('TheScienceTourChallengeBundle:Challenge')
        ->findNonFuture($isErasmus);

      $form->add('challenge', 'document', array(
        'class'       => 'TheScienceTour\ChallengeBundle\Document\Challenge',
        'property'    => 'titleForChoiceList',
        'empty_value' => 'Aucun',
        'empty_data'  => NULL,
        'choices'     => $challengeList,
        'required'    => FALSE
      ));
    }

    $form->add('picture', 'sonata_media_type', array(
      'provider' => 'sonata.media.provider.image',
      'context'  => 'project',
      'required' => $edit && $project->getPicture()
    ));

    $form->add('goal', 'purified_textarea')
      ->add('description', 'purified_textarea')
      ->add('duration', 'integer', array('attr' => array('min' => 1)))
      ->add('durationUnit', 'choice', array(
        'choices' => array(
          'day'   => $this->get('translator')->trans('Days'),
          'week'  => $this->get('translator')->trans('Weeks'),
          'month' => $this->get('translator')->trans('Months')
        )
      ))
      ->add('price', 'integer', array(
        'attr'     => array('min' => 0),
        'required' => FALSE
      ))
      ->add('startedAt', 'date', array(
        'empty_value' => '',
        'required'    => FALSE
      ));

    // No resources for Erasmus.
    if (!$isErasmus) {
      $form->add('tools', 'collection', array(
        'type'         => new ResourceType(),
        'allow_add'    => TRUE,
        'allow_delete' => TRUE,
        'by_reference' => FALSE,
      ))
        ->add('materials', 'collection', array(
          'type'         => new ResourceType(),
          'allow_add'    => TRUE,
          'allow_delete' => TRUE,
          'by_reference' => FALSE,
        ))
        ->add('premises', 'collection', array(
          'type'         => new ResourceType(),
          'allow_add'    => TRUE,
          'allow_delete' => TRUE,
          'by_reference' => FALSE,
        ))
        ->add('skills', 'collection', array(
          'type'         => new SkillType(),
          'allow_add'    => TRUE,
          'allow_delete' => TRUE,
          'by_reference' => FALSE,
        ));
    }

    if (!$edit || $project->getStatus() == 0) {
      $form->add('draft', 'submit', array(
        'attr' => array(
          'formnovalidate' => 'formnovalidate',
          'class'          => 'button white_button'
        )
      ))
        ->add('publish', 'submit', array(
          'attr'              => array('class' => 'button orange_button'),
          'validation_groups' => array(
            'Default',
            'publish'
          )
        ));
    }
    else {
      $form->add('save', 'submit', array(
        'attr'              => array('class' => 'button orange_button'),
        'validation_groups' => array(
          'Default',
          'publish'
        )
      ));
    }

    return $form;
  }

  public function addProjectAction($idchallenge) {
    $user = $this->getUser();
    if (!$user) {
      throw new AccessDeniedException();
    }
    // Get erasmus site status.
    $session   = $this->get('session');
    $isErasmus = $session->get('isErasmus', FALSE);

    $project = new Project();
    $project->setCreator($user);
    if ($idchallenge) {
      $challenge = $this->get('doctrine_mongodb')
        ->getRepository('TheScienceTourChallengeBundle:Challenge')
        ->find($idchallenge);
      if (!$challenge) {
        throw $this->createNotFoundException('Aucun défi trouvé avec l\'id ' . $idchallenge);
      }
      $project->setChallenge($challenge);
    }

    $form = $this->_formProjectAction($project);

    // Build form.
    $form    = $form->getForm();
    $request = $this->get('request');
    if ($request->getMethod() == 'POST') {
      // Save erasmus project.
      $project->setIsErasmus($this->get('session')->get('isErasmus'));

      $form->bind($request);
      if ($form->isValid()) {

        if (!$project->getPicture()->getSize()) {
          $project->setPicture(NULL);
        }
        if ($form->get('draft')->isClicked()) {
          $project->setStatus(0);
        }
        else {
          $project->setStatus(1);
          $project->setPublishedAt(new \Datetime);
        }
        $project->updateFinishedAt();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($project);
        $dm->flush();
        if ($form->get('draft')->isClicked()) {
          return $this->redirect($this->generateUrl('fos_user_profile_show', array('tab' => "mydrafts")));
        }

        $dm   = $this->get('doctrine_mongodb')->getManager();
        $news = new News();
        $news->setProjectId($project->getId());
        $news->setAuthor($user);
        $news->setTitle("{% publishProject %}");


        $dm->persist($news);
        foreach ($project->getTools() as $tool) {
          if ($tool->getCreatorHelpNb() < 0 || $tool->getCreatorHelpNb() > $tool->getNumber()) {
            $tool->setCreatorHelpNb(0);
          }
          if ($tool->getCreatorHelpNb() > 0) {
            $news = new News();
            $news->setProjectId($project->getId());
            $news->setAuthor($user);
            $news->setTitle("{% giveTool %}");
            $news->setContent($tool->getName() . '{%#%}' . $tool->getCreatorHelpNb());
            $dm->persist($news);
          }
        }
        foreach ($project->getMaterials() as $material) {
          if ($material->getCreatorHelpNb() < 0 || $material->getCreatorHelpNb() > $material->getNumber()) {
            $material->setCreatorHelpNb(0);
          }
          if ($material->getCreatorHelpNb() > 0) {
            $news = new News();
            $news->setProjectId($project->getId());
            $news->setAuthor($user);
            $news->setTitle("{% giveMaterial %}");
            $news->setContent($material->getName() . '{%#%}' . $material->getCreatorHelpNb());
            $dm->persist($news);
          }
        }
        foreach ($project->getPremises() as $premise) {
          if ($premise->getCreatorHelpNb() < 0 || $premise->getCreatorHelpNb() > $premise->getNumber()) {
            $premise->setCreatorHelpNb(0);
          }
          if ($premise->getCreatorHelpNb() > 0) {
            $news = new News();
            $news->setProjectId($project->getId());
            $news->setAuthor($user);
            $news->setTitle("{% givePremise %}");
            $news->setContent($premise->getName() . '{%#%}' . $premise->getCreatorHelpNb());
            $dm->persist($news);
          }
        }
        $challenge = $project->getChallenge();
        if ($challenge) {
          $challengeCreator = $challenge->getCreator();
          $challengeCreator->addNotification("challenge-newproject", $challenge->getId());
          $dm->persist($challengeCreator);
        }
        $dm->flush();

        return $this->redirect($this->generateUrl('tst_project', array('id' => $project->getId())));
      }
    }

    return $this->render('TheScienceTourProjectBundle::add.html.twig', array(
      'message'    => $this->get('translator')
        ->trans('All texts must be written in English. You can then translate the project into several languages.'),
      'isErasmus'  => $isErasmus,
      'form'       => $form->createView(),
      'isEditForm' => FALSE,
      'isAddForm'  => TRUE
    ));

  }

  public function editProjectAction($id) {
    $user = $this->getUser();
    if (!$user) {
    //   throw new AccessDeniedException();
    }
    // Get erasmus site status.
    $session   = $this->get('session');
    $isErasmus = $session->get('isErasmus', FALSE);

    $project = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Project')
      ->find($id);

    if (!$project) {
      throw $this->createNotFoundException('Aucun projet trouvé avec l\'id ' . $id);
    }
    if ($user != $project->getCreator() && !($this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD'))
    ) {
    //   throw new AccessDeniedException();
    }

    $originalChallenge = $project->getChallenge();

    $originalTools                  = array();
    $originalToolsCreatorHelpNb     = array();
    $originalMaterials              = array();
    $originalMaterialsCreatorHelpNb = array();
    $originalPremises               = array();
    $originalPremisesCreatorHelpNb  = array();
    $originalSkills                 = array();

    foreach ($project->getTools() as $tool) {
      $originalTools[]              = $tool;
      $originalToolsCreatorHelpNb[] = $tool->getCreatorHelpNb();
    }
    foreach ($project->getMaterials() as $material) {
      $originalMaterials[]              = $material;
      $originalMaterialsCreatorHelpNb[] = $material->getCreatorHelpNb();
    }
    foreach ($project->getPremises() as $premise) {
      $originalPremises[]              = $premise;
      $originalPremisesCreatorHelpNb[] = $premise->getCreatorHelpNb();
    }
    foreach ($project->getSkills() as $skill) {
      $originalSkills[] = $skill;
    }

    $form = $this->_formProjectAction($project, true)->getForm();
    $form_languages = $this->createForm(
        new TranslationLanguageType,
        new ProjectTranslation,
        [
            // 'choices' => $this->container->getParameter('erasmusLanguages'),
            'empty_value' => '-- ' . $this->get('translator')->trans('Translate to...') . ' --'
        ]
    );

    $request = $this->get('request');
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

        foreach ($project->getTools() as $tool) {
          if ($tool->getCreatorHelpNb() < 0 || $tool->getCreatorHelpNb() > $tool->getNumber()) {
            $tool->setCreatorHelpNb(0);
          }
          if ($project->getStatus() == 1) {
            if ($tool->getCreatorHelpNb() != 0) {
              $found = FALSE;
              foreach ($originalTools as $key => $originalTool) {
                if ($originalTool->getId() === $tool->getId()) {
                  $found = TRUE;
                  if ($tool->getCreatorHelpNb() != $originalToolsCreatorHelpNb[$key]) {
                    $news = new News();
                    $news->setProjectId($project->getId());
                    $news->setAuthor($user);
                    $news->setTitle("{% giveTool %}");
                    $news->setContent($tool->getName() . '{%#%}' . $tool->getCreatorHelpNb());
                    $dm->persist($news);
                    foreach ($project->getEverybody() as $member) {
                      if ($member != $user) {
                        $member->addNotification("project-news", $project->getId());
                        $dm->persist($member);
                      }
                    }
                  }
                  break;
                }
              }
              if (!$found) {
                $news = new News();
                $news->setProjectId($project->getId());
                $news->setAuthor($user);
                $news->setTitle("{% giveTool %}");
                $news->setContent($tool->getName() . '{%#%}' . $tool->getCreatorHelpNb());
                $dm->persist($news);
                foreach ($project->getEverybody() as $member) {
                  if ($member != $user) {
                    $member->addNotification("project-news", $project->getId());
                    $dm->persist($member);
                  }
                }
              }
            }
          }
        }

        foreach ($project->getMaterials() as $material) {
          if ($material->getCreatorHelpNb() < 0 || $material->getCreatorHelpNb() > $material->getNumber()) {
            $material->setCreatorHelpNb(0);
          }
          if ($project->getStatus() == 1) {
            if ($material->getCreatorHelpNb() != 0) {
              $found = FALSE;
              foreach ($originalMaterials as $key => $originalMaterial) {
                if ($originalMaterial->getId() === $material->getId()) {
                  $found = TRUE;
                  if ($material->getCreatorHelpNb() != $originalMaterialsCreatorHelpNb[$key]) {
                    $news = new News();
                    $news->setProjectId($project->getId());
                    $news->setAuthor($user);
                    $news->setTitle("{% giveMaterial %}");
                    $news->setContent($material->getName() . '{%#%}' . $material->getCreatorHelpNb());
                    $dm->persist($news);
                    foreach ($project->getEverybody() as $member) {
                      if ($member != $user) {
                        $member->addNotification("project-news", $project->getId());
                        $dm->persist($member);
                      }
                    }
                  }
                  break;
                }
              }
              if (!$found) {
                $news = new News();
                $news->setProjectId($project->getId());
                $news->setAuthor($user);
                $news->setTitle("{% giveMaterial %}");
                $news->setContent($material->getName() . '{%#%}' . $material->getCreatorHelpNb());
                $dm->persist($news);
                foreach ($project->getEverybody() as $member) {
                  if ($member != $user) {
                    $member->addNotification("project-news", $project->getId());
                    $dm->persist($member);
                  }
                }
              }
            }
          }
        }

        foreach ($project->getPremises() as $premise) {
          if ($premise->getCreatorHelpNb() < 0 || $premise->getCreatorHelpNb() > $premise->getNumber()) {
            $premise->setCreatorHelpNb(0);
          }
          if ($project->getStatus() == 1) {
            if ($premise->getCreatorHelpNb() != 0) {
              $found = FALSE;
              foreach ($originalPremises as $key => $originalPremise) {
                if ($originalPremise->getId() === $premise->getId()) {
                  $found = TRUE;
                  if ($premise->getCreatorHelpNb() != $originalPremisesCreatorHelpNb[$key]) {
                    $news = new News();
                    $news->setProjectId($project->getId());
                    $news->setAuthor($user);
                    $news->setTitle("{% givePremise %}");
                    $news->setContent($premise->getName() . '{%#%}' . $premise->getCreatorHelpNb());
                    $dm->persist($news);
                    foreach ($project->getEverybody() as $member) {
                      if ($member != $user) {
                        $member->addNotification("project-news", $project->getId());
                        $dm->persist($member);
                      }
                    }
                  }
                  break;
                }
              }
              if (!$found) {
                $news = new News();
                $news->setProjectId($project->getId());
                $news->setAuthor($user);
                $news->setTitle("{% givePremise %}");
                $news->setContent($premise->getName() . '{%#%}' . $premise->getCreatorHelpNb());
                $dm->persist($news);
                foreach ($project->getEverybody() as $member) {
                  if ($member != $user) {
                    $member->addNotification("project-news", $project->getId());
                    $dm->persist($member);
                  }
                }
              }
            }
          }
        }


        foreach ($project->getTools() as $tool) {
          foreach ($originalTools as $key => $toDel) {
            if ($toDel->getId() === $tool->getId()) {
              unset($originalTools[$key]);
            }
          }
        }
        foreach ($project->getMaterials() as $material) {
          foreach ($originalMaterials as $key => $toDel) {
            if ($toDel->getId() === $material->getId()) {
              unset($originalMaterials[$key]);
            }
          }
        }
        foreach ($project->getPremises() as $premise) {
          foreach ($originalPremises as $key => $toDel) {
            if ($toDel->getId() === $premise->getId()) {
              unset($originalPremises[$key]);
            }
          }
        }
        foreach ($project->getSkills() as $skill) {
          foreach ($originalSkills as $key => $toDel) {
            if ($toDel->getId() === $skill->getId()) {
              unset($originalSkills[$key]);
            }
          }
        }
        foreach ($originalTools as $tool) {
          $dm->remove($tool);
        }
        foreach ($originalMaterials as $material) {
          $dm->remove($material);
        }
        foreach ($originalPremises as $premise) {
          $dm->remove($premise);
        }
        foreach ($originalSkills as $skill) {
          $dm->remove($skill);
        }
        $project->setSupporters();
        if ($project->getStatus() == 0) {
          if ($form->get('publish')->isClicked()) {
            $project->setStatus(1);
            $project->setPublishedAt(new \Datetime);
            $news = new News();
            $news->setProjectId($project->getId());
            $news->setAuthor($user);
            $news->setTitle("{% publishProject %}");
            $dm->persist($news);
            foreach ($project->getTools() as $tool) {
              if ($tool->getCreatorHelpNb() > 0) {
                $news = new News();
                $news->setProjectId($project->getId());
                $news->setAuthor($user);
                $news->setTitle("{% giveTool %}");
                $news->setContent($tool->getName() . '{%#%}' . $tool->getCreatorHelpNb());
                $dm->persist($news);
                foreach ($project->getEverybody() as $member) {
                  if ($member != $user) {
                    $member->addNotification("project-news", $project->getId());
                    $dm->persist($member);
                  }
                }
              }
            }
            foreach ($project->getMaterials() as $material) {
              if ($material->getCreatorHelpNb() > 0) {
                $news = new News();
                $news->setProjectId($project->getId());
                $news->setAuthor($user);
                $news->setTitle("{% giveMaterial %}");
                $news->setContent($material->getName() . '{%#%}' . $material->getCreatorHelpNb());
                $dm->persist($news);
                foreach ($project->getEverybody() as $member) {
                  if ($member != $user) {
                    $member->addNotification("project-news", $project->getId());
                    $dm->persist($member);
                  }
                }
              }
            }
            foreach ($project->getPremises() as $premise) {
              if ($premise->getCreatorHelpNb() > 0) {
                $news = new News();
                $news->setProjectId($project->getId());
                $news->setAuthor($user);
                $news->setTitle("{% givePremise %}");
                $news->setContent($premise->getName() . '{%#%}' . $premise->getCreatorHelpNb());
                $dm->persist($news);
                foreach ($project->getEverybody() as $member) {
                  if ($member != $user) {
                    $member->addNotification("project-news", $project->getId());
                    $dm->persist($member);
                  }
                }
              }
            }
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
    $lang         = $this->container->getParameter('erasmusLanguages');
    return $this->render('TheScienceTourProjectBundle::edit.html.twig', array(
      'message'    => (isset($_GET['lang']) && isset($lang[$_GET['lang']]) ? $this->get('translator')->trans('You are currently editing content in:') . ' ' . $lang[$_GET['lang']] : ''),
      'project'    => $project,
      'form'       => $form->createView(),
      'translation_form'=> $form_languages->createView(),
      'isErasmus'  => $isErasmus,
      'isEditForm' => TRUE,
      'isAddForm'  => FALSE
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

  public function adminProjectChatAction($id, $idres, $idhelp) {
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

    $res = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Resource')
      ->find($idres);
    if (!$res) {
      throw $this->createNotFoundException('Aucune ressource trouvée avec l\'id ' . $idres);
    }
    if (!$project->getTools()->contains($res) && !$project->getMaterials()
        ->contains($res) && !$project->getPremises()->contains($res)
    ) {
      throw $this->createNotFoundException('La ressource ' . $idres . ' n\'appartient pas au projet ' . $id);
    }
    $idhelp = intval($idhelp);
    if ($idhelp <= 0 && $res->getUncompletedHelps()->count() <= $idhelp) {
      throw $this->createNotFoundException('idhelp non valide');
    }

    $help = $res->getUncompletedHelps()->get($idhelp);

    if ($help->getChat() == NULL) {
      $dm   = $this->get('doctrine_mongodb')->getManager();
      $chat = new Chat();
      $chat->addUser($user);
      $chat->addUser($help->getHelper());
      $chat->setTitle($project->getTitle() . ' - ' . $help->getNbProposed() . ' x ' . $res->getName());
      $dm->persist($chat);
      $help->setChat($chat);
      $dm->flush();
    }
    else {
      $dm = $this->container->get('doctrine_mongodb')->getManager();
      $user->removeNotification("chat", $help->getChat()->getId());
      $dm->persist($user);
      $dm->flush();
    }

    $request = $this->get('request');

    if ($request->getMethod() == 'POST') {
      if ($request->request->has('stop')) {
        $from = $this->container->getParameter('mailer_sender_address');

        $message = \Swift_Message::newInstance()
          ->setSubject('The Science Tour : Annulation d\'aide au projet ' . $project->getTitle())
          ->setFrom($from)
          ->setTo($help->getHelper()->getEmail())
          ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resDeleteHelpMail.txt.twig', array(
            'project' => $project,
            'res'     => $res,
            'help'    => $help
          )));

        $this->get('mailer')->send($message);

        $dm = $this->get('doctrine_mongodb')->getManager();

        $help->getHelper()->removeNotification("chat", $help->getChat()
          ->getId());
        $dm->persist($help->getHelper());

        $dm->remove($help->getChat());
        $res->removeHelp($help);
        $dm->flush();

        return $this->redirect($this->generateUrl('tst_project_admin', array('id' => $project->getId())));

      }
      else if ($request->request->has('play')) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $help->getHelper()->removeNotification("chat", $help->getChat()
          ->getId());
        $dm->persist($help->getHelper());

        if ($request->request->has('nb')) {
          $help->setNbNeeded(intval($request->request->get('nb')));
        }
        $help->setNbReceived($help->getNbNeeded());

        $dm->remove($help->getChat());
        $help->setChat(NULL);

        $from = $this->container->getParameter('mailer_sender_address');

        $message = \Swift_Message::newInstance()
          ->setSubject('The Science Tour : Don reçu pour le projet ' . $project->getTitle())
          ->setFrom($from)
          ->setTo($help->getHelper()->getEmail())
          ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resCompletedHelpMail.txt.twig', array(
            'project' => $project,
            'res'     => $res,
            'help'    => $help
          )));

        $this->get('mailer')->send($message);

        $news = new News();
        $news->setProjectId($project->getId());
        $news->setAuthor($help->getHelper());
        if ($project->getTools()->contains($res)) {
          $news->setTitle("{% giveTool %}");
        }
        else if ($project->getMaterials()->contains($res)) {
          $news->setTitle("{% giveMaterial %}");
        }
        else {
          $news->setTitle("{% givePremise %}");
        }
        $news->setContent($res->getName() . '{%#%}' . intval($help->getNbReceived()));
        $dm->persist($news);
        foreach ($project->getEverybody() as $member) {
          if ($member != $user) {
            $member->addNotification("project-news", $project->getId());
            $dm->persist($member);
          }
        }
        $dm->flush();

        return $this->redirect($this->generateUrl('tst_project_admin', array('id' => $project->getId())));

      }
      else if ($request->request->has('pause')) {
        if ($request->request->has('nb')) {
          $dm = $this->get('doctrine_mongodb')->getManager();
          $help->setNbNeeded(intval($request->request->get('nb')));

          $message = new Message();
          $message->addUnreadBy($help->getHelper());
          if ($help->getNbProposed() == $help->getNbNeeded()) {
            $message->setContent("Message automatique : Votre proposition de don de " . $help->getNbNeeded() . " " . $res->getName() . " a été acceptée.");
          }
          else {
            $message->setContent("Message automatique : Votre proposition de don a été acceptée. Il n'y aura cependant besoin que de " . $help->getNbNeeded() . " " . $res->getName() . ", au lieu des " . $help->getNbProposed() . " proposés.");
          }
          $help->getChat()->addMessage($message);

          $from = $this->container->getParameter('mailer_sender_address');

          $message = \Swift_Message::newInstance()
            ->setSubject('The Science Tour : Nouveau message')
            ->setFrom($from)
            ->setTo($help->getHelper()->getEmail())
            ->setBody($this->renderView('TheScienceTourMessageBundle::newMessageMail.txt.twig', array(
              'chat'   => $help->getChat(),
              'author' => NULL
            )));

          $help->getHelper()->addNotification("chat", $help->getChat()
            ->getId());
          $dm->persist($help->getHelper());
          $dm->flush();

          $this->get('mailer')->send($message);

          $dm->flush();
        }
      }
    }

    return $this->render('TheScienceTourProjectBundle:admin:adminChat.html.twig', array(
      'project' => $project,
      'res'     => $res,
      'help'    => $help
    ));

  }


  /*
	 *  NEWS ACTIONS
	 */

  public function addNewsAction($id) {
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

    if (!$project->getTeam()
        ->contains($user) && !($this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD'))
    ) {
      throw new AccessDeniedException();
    }

    $news = new News();
    $news->setProjectId($id);
    $news->setAuthor($user);
    $form = $this->createFormBuilder($news)
      ->add('title', 'text')
      ->add('picture', 'sonata_media_type', array(
        'provider' => 'sonata.media.provider.image',
        'context'  => 'news',
        'required' => FALSE
      ))
      ->add('content', 'purified_textarea')
      ->getForm();
    $project->setUpdatedAt(new \Datetime);
    $request = $this->get('request');
    if ($request->getMethod() == 'POST') {
      $form->bind($request);
      if ($form->isValid()) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($project);
        $dm->persist($news);
        foreach ($project->getEverybody() as $member) {
          if ($member != $user) {
            $member->addNotification("project-news", $project->getId());
            $dm->persist($member);
          }
        }
        $dm->flush();

        if ($project->getSubscribers() && $project->getSubscribers()
            ->count() > 0
        ) {

          $from = $this->container->getParameter('mailer_sender_address');

          $message = \Swift_Message::newInstance()
            ->setSubject('The Science Tour : Nouvel article dans ' . $project->getTitle())
            ->setFrom($from)
            ->setTo($from)
            ->setBody($this->renderView('TheScienceTourProjectBundle:subscribers:newsMail.txt.twig', array(
              'project' => $project
            )));
          foreach ($project->getSubscribers() as $subscriber) {
            $message->addBcc($subscriber->getEmail());
          }

          $this->get('mailer')->send($message);

        }

        return $this->redirect($this->generateUrl('tst_project', array(
          'id'  => $project->getId(),
          'tab' => "news"
        )));
      }
    }

    return $this->render('TheScienceTourProjectBundle::addNews.html.twig', array(
      'project' => $project,
      'form'    => $form->createView()
    ));

  }

  public function editNewsAction($id, $idnews) {
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

    if (!$project->getTeam()
        ->contains($user) && !($this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD'))
    ) {
      throw new AccessDeniedException();
    }

    $news = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:News')
      ->find($idnews);
    if (!$news) {
      throw $this->createNotFoundException('Aucun article trouvé avec l\'id ' . $idnews);
    }
    if ($news->getProjectId() != $id) {
      throw $this->createNotFoundException('L\'article ' . $idnews . ' n\'appartient pas au projet ' . $id);
    }

    if ($news->getTitle() == "{% publishProject %}" || $news->getTitle() == "{% giveTool %}" || $news->getTitle() == "{% giveMaterial %}" || $news->getTitle() == "{% givePremise %}" || $news->getTitle() == "{% giveSkill %}") {
      throw $this->createNotFoundException('Impossible de modifier un article spécial');
    }

    if ($user != $project->getCreator() && !($this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD')) && $user != $news->getAuthor()
    ) {
      throw new AccessDeniedException();
    }

    $form = $this->createFormBuilder($news)
      ->add('title', 'text')
      ->add('picture', 'sonata_media_type', array(
        'provider' => 'sonata.media.provider.image',
        'context'  => 'news',
        'required' => FALSE
      ))
      ->add('content', 'purified_textarea')
      ->getForm();

    $request = $this->get('request');
    if ($request->getMethod() == 'POST') {
      $project->setUpdatedAt(new \Datetime);
      if ($request->request->has('delete')) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($project);
        $dm->remove($news);
        $dm->flush();
        return $this->redirect($this->generateUrl('tst_project', array(
          'id'  => $project->getId(),
          'tab' => "news"
        )));
      }
      else {
        $form->bind($request);
        if ($form->isValid()) {
          $dm = $this->get('doctrine_mongodb')->getManager();
          $dm->persist($project);
          $dm->persist($news);
          $dm->flush();
          return $this->redirect($this->generateUrl('tst_project', array(
            'id'  => $project->getId(),
            'tab' => "news"
          )));
        }
      }
    }

    return $this->render('TheScienceTourProjectBundle::editNews.html.twig', array(
      'form'    => $form->createView(),
      'project' => $project,
      'news'    => $news
    ));

  }

  public function deleteNewsAction($id, $idnews) {
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

    $news = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:News')
      ->find($idnews);
    if (!$news) {
      throw $this->createNotFoundException('Aucun article trouvé avec l\'id ' . $idnews);
    }
    if ($news->getProjectId() != $id) {
      throw $this->createNotFoundException('L\'article ' . $idnews . ' n\'appartient pas au projet ' . $id);
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $dm->persist($project);
    $dm->remove($news);
    $dm->flush();
    return $this->redirect($this->generateUrl('tst_project', array(
      'id'  => $project->getId(),
      'tab' => "news"
    )));

  }

  /*
	 *  CONTRIBUTE ACTIONS
	*/

  public function contributeAction($id) {
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
    if ($user == $project->getCreator()) {
      throw $this->createNotFoundException('Vous êtes le créateur du projet ' . $id);
    }
    if ($project->getContributors()->contains($user)) {
      throw $this->createNotFoundException('Vous participez déjà à ce projet !');
    }

    $from = $this->container->getParameter('mailer_sender_address');

    $message = \Swift_Message::newInstance()
      ->setSubject('The Science Tour : Nouvelle demande de participation au projet ' . $project->getTitle())
      ->setFrom($from)
      ->setTo($project->getCreator()->getEmail())
      ->setBody($this->renderView('TheScienceTourProjectBundle:contributors:contributeMail.txt.twig', array(
        'user'    => $user,
        'project' => $project
      )));
    $this->get('mailer')->send($message);

    return $this->render('TheScienceTourProjectBundle:contributors:contribute.html.twig', array(
      'project' => $project
    ));
  }

  public function activateContributorAction($id, $idcontrib) {
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
    if ($user != $project->getCreator()) {
      throw $this->createNotFoundException('Vous n\'êtes pas le créateur de ce projet');
    }
    $contributor = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourUserBundle:User')
      ->find($idcontrib);
    if (!$contributor) {
      throw $this->createNotFoundException('Aucun utilisateur trouvé avec l\'id ' . $idcontrib);
    }
    if ($project->getContributors()->contains($contributor)) {
      throw $this->createNotFoundException('L\'utilisateur ' . $contributor->getUsername() . ' participe déjà à votre projet !');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $project->addContributor($contributor);
    $project->setUpdatedAt(new \Datetime);
    $dm->persist($project);
    $dm->flush();

    $from = $this->container->getParameter('mailer_sender_address');

    $message = \Swift_Message::newInstance()
      ->setSubject('The Science Tour : Participation au projet ' . $project->getTitle())
      ->setFrom($from)
      ->setTo($contributor->getEmail())
      ->setBody($this->renderView('TheScienceTourProjectBundle:contributors:activateContributorMail.txt.twig', array(
        'contributor' => $contributor,
        'project'     => $project
      )));
    $this->get('mailer')->send($message);

    return $this->render('TheScienceTourProjectBundle:contributors:activateContributor.html.twig', array(
      'contributor' => $contributor,
      'project'     => $project
    ));
  }

  public function uncontributeAction($id) {
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
    if ($user == $project->getCreator()) {
      throw $this->createNotFoundException('Vous êtes le créateur du projet ' . $id);
    }
    if (!$project->getContributors()->contains($user)) {
      throw $this->createNotFoundException('Vous ne participez pas à ce projet !');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $project->removeContributor($user);
    $project->setUpdatedAt(new \Datetime);
    $dm->persist($project);
    $dm->flush();

    $from = $this->container->getParameter('mailer_sender_address');

    $message = \Swift_Message::newInstance()
      ->setSubject('The Science Tour : Annulation de participation au projet ' . $project->getTitle())
      ->setFrom($from)
      ->setTo($project->getCreator()->getEmail())
      ->setBody($this->renderView('TheScienceTourProjectBundle:contributors:uncontributeMail.txt.twig', array(
        'user'    => $user,
        'project' => $project
      )));
    $this->get('mailer')->send($message);

    return $this->render('TheScienceTourProjectBundle:contributors:uncontribute.html.twig', array(
      'project' => $project
    ));
  }

  /*
	 *  SUBSCRIBE ACTIONS
	 */

  public function subscribeAction($id) {

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
    if ($user == $project->getCreator()) {
      throw $this->createNotFoundException('Vous êtes le créateur du projet ' . $id);
    }
    if ($project->getSubscribers()->contains($user)) {
      throw $this->createNotFoundException('Vous êtes déjà abonné à ce projet !');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $project->addSubscriber($user);
    $dm->persist($project);
    $dm->flush();

    return $this->render('TheScienceTourProjectBundle:subscribers:subscribe.html.twig', array(
      'project' => $project
    ));

  }

  public function unsubscribeAction($id) {
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
    if ($user == $project->getCreator()) {
      throw $this->createNotFoundException('Vous êtes le créateur du projet ' . $id);
    }
    if (!$project->getSubscribers()->contains($user)) {
      throw $this->createNotFoundException('Vous n\'êtes pas abonné à ce projet !');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $project->removeSubscriber($user);
    $dm->persist($project);
    $dm->flush();

    return $this->render('TheScienceTourProjectBundle:subscribers:unsubscribe.html.twig', array(
      'project' => $project
    ));
  }

  /*
	 *  SKILL HELP ACTIONS
	 */

  public function skillHelpAction($id, $idskill) {
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

    $skill = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Skill')
      ->find($idskill);
    if (!$skill) {
      throw $this->createNotFoundException('Aucune compétence trouvée avec l\'id ' . $idskill);
    }
    if (!$project->getSkills()->contains($skill)) {
      throw $this->createNotFoundException('La compétence ' . $idskill . ' n\'appartient pas au projet ' . $id);
    }
    if ($skill->getHelpers()->contains($user)) {
      throw $this->createNotFoundException('Vous aidez déjà le projet avec cette compétence');
    }
    if ($skill->getNumber() == $skill->getHelpers()->count()) {
      throw $this->createNotFoundException('Il y a déjà assez de personnes qui aident pour cette compétence');
    }


    if ($user == $project->getCreator()) {
      $dm = $this->get('doctrine_mongodb')->getManager();
      $skill->addHelper($user);
      $project->setUpdatedAt(new \Datetime);

      $news = new News();
      $news->setProjectId($project->getId());
      $news->setAuthor($user);
      $news->setTitle("{% giveSkill %}");
      $news->setContent($skill->getName());
      $dm->persist($news);
      foreach ($project->getEverybody() as $member) {
        if ($member != $user) {
          $member->addNotification("project-news", $project->getId());
          $dm->persist($member);
        }
      }

      $dm->persist($skill);
      $dm->persist($project);
      $dm->flush();

      return $this->render('TheScienceTourProjectBundle:skillHelpers:activateSkillHelper.html.twig', array(
        'helper'  => $user,
        'skill'   => $skill,
        'project' => $project
      ));
    }
    else {

      $dm = $this->get('doctrine_mongodb')->getManager();
      $project->addSubscriber($user);
      $dm->persist($project);
      $dm->flush();

      $from = $this->container->getParameter('mailer_sender_address');

      $message = \Swift_Message::newInstance()
        ->setSubject('The Science Tour : Nouvelle proposition d\'aide au projet ' . $project->getTitle())
        ->setFrom($from)
        ->setTo($project->getCreator()->getEmail())
        ->setBody($this->renderView('TheScienceTourProjectBundle:skillHelpers:skillHelpMail.txt.twig', array(
          'user'    => $user,
          'skill'   => $skill,
          'project' => $project
        )));
      $this->get('mailer')->send($message);

      return $this->render('TheScienceTourProjectBundle:skillHelpers:skillHelp.html.twig', array(
        'skill'   => $skill,
        'project' => $project
      ));
    }
  }

  public function activateSkillHelperAction($id, $idskill, $idhelper) {
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
    if ($user != $project->getCreator()) {
      throw $this->createNotFoundException('Vous n\'êtes pas le créateur de ce projet');
    }

    $skill = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Skill')
      ->find($idskill);
    if (!$skill) {
      throw $this->createNotFoundException('Aucune compétence trouvée avec l\'id ' . $idskill);
    }
    if (!$project->getSkills()->contains($skill)) {
      throw $this->createNotFoundException('La compétence ' . $idskill . ' n\'appartient pas au projet ' . $id);
    }
    if ($skill->getNumber() == $skill->getHelpers()->count()) {
      throw $this->createNotFoundException('Il y a déjà assez de personnes qui aident pour cette compétence');
    }

    $helper = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourUserBundle:User')
      ->find($idhelper);
    if (!$helper) {
      throw $this->createNotFoundException('Aucun utilisateur trouvé avec l\'id ' . $idhelper);
    }
    if ($skill->getHelpers()->contains($helper)) {
      throw $this->createNotFoundException('L\'utilisateur ' . $helper->getUsername() . ' aide déjà le projet avec cette compétence');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $skill->addHelper($helper);
    $project->setUpdatedAt(new \Datetime);

    $news = new News();
    $news->setProjectId($project->getId());
    $news->setAuthor($helper);
    $news->setTitle("{% giveSkill %}");
    $news->setContent($skill->getName());
    $dm->persist($news);
    foreach ($project->getEverybody() as $member) {
      if ($member != $user) {
        $member->addNotification("project-news", $project->getId());
        $dm->persist($member);
      }
    }

    $dm->persist($skill);
    $dm->persist($project);
    $dm->flush();

    $from = $this->container->getParameter('mailer_sender_address');

    $message = \Swift_Message::newInstance()
      ->setSubject('The Science Tour : Aide au projet ' . $project->getTitle())
      ->setFrom($from)
      ->setTo($helper->getEmail())
      ->setBody($this->renderView('TheScienceTourProjectBundle:skillHelpers:activateSkillHelperMail.txt.twig', array(
        'helper'  => $helper,
        'skill'   => $skill,
        'project' => $project
      )));
    $this->get('mailer')->send($message);

    return $this->render('TheScienceTourProjectBundle:skillHelpers:activateSkillHelper.html.twig', array(
      'helper'  => $helper,
      'skill'   => $skill,
      'project' => $project
    ));
  }

  public function skillUnHelpAction($id, $idskill) {
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

    $skill = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Skill')
      ->find($idskill);
    if (!$skill) {
      throw $this->createNotFoundException('Aucune compétence trouvée avec l\'id ' . $idskill);
    }
    if (!$project->getSkills()->contains($skill)) {
      throw $this->createNotFoundException('La compétence ' . $idskill . ' n\'appartient pas au projet ' . $id);
    }
    if (!$skill->getHelpers()->contains($user)) {
      throw $this->createNotFoundException('Vous n\'aidez pas le projet avec cette compétence !');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $skill->removeHelper($user);
    $project->setUpdatedAt(new \Datetime);
    $dm->persist($skill);
    $dm->persist($project);
    $dm->flush();

    if ($user != $project->getCreator()) {

      $from = $this->container->getParameter('mailer_sender_address');

      $message = \Swift_Message::newInstance()
        ->setSubject('The Science Tour : Annulation d\'aide au projet ' . $project->getTitle())
        ->setFrom($from)
        ->setTo($project->getCreator()->getEmail())
        ->setBody($this->renderView('TheScienceTourProjectBundle:skillHelpers:skillUnhelpMail.txt.twig', array(
          'user'    => $user,
          'skill'   => $skill,
          'project' => $project
        )));
      $this->get('mailer')->send($message);
    }

    return $this->render('TheScienceTourProjectBundle:skillHelpers:skillUnhelp.html.twig', array(
      'skill'   => $skill,
      'project' => $project
    ));
  }

  /*
	 *  TOOLS/MATERIALS/PREMISES HELP ACTIONS
	 */

  public function resHelpAction($id, $idres) {
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
    if ($user == $project->getCreator()) {
      throw $this->createNotFoundException('Vous êtes le créateur du projet ' . $id);
    }

    $res = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Resource')
      ->find($idres);
    if (!$res) {
      throw $this->createNotFoundException('Aucune ressource trouvée avec l\'id ' . $idres);
    }
    if (!$project->getTools()->contains($res) && !$project->getMaterials()
        ->contains($res) && !$project->getPremises()->contains($res)
    ) {
      throw $this->createNotFoundException('La ressource ' . $idres . ' n\'appartient pas au projet ' . $id);
    }
    if ($res->containsHelper($user)) {
      throw $this->createNotFoundException('Vous aidez déjà le projet pour cette ressource');
    }
    if ($res->getNumber() == $res->getActualNumber()) {
      throw $this->createNotFoundException('Il y a déjà assez de personnes qui aident pour cette ressource');
    }

    $error_message = NULL;

    $request = $this->get('request');
    if ($request->getMethod() == 'POST') {

      $number = intval($request->request->get('number'));

      if ($number > 0 && $res->getActualNumber() + $number <= $res->getNumber()) {

        $dm   = $this->get('doctrine_mongodb')->getManager();
        $help = new Help();
        $help->setHelper($user);
        $help->setNbProposed($number);
        $res->addHelp($help);
        $project->setUpdatedAt(new \Datetime);

        $mess = $request->request->get('message');

        if ($mess != NULL && $mess != "") {
          $chat = new Chat();
          $chat->addUser($user);
          $chat->addUser($project->getCreator());
          $chat->setTitle($project->getTitle() . ' - ' . $number . ' x ' . $res->getName());
          $message = new Message();
          $message->addUnreadBy($project->getCreator());
          $message->setAuthor($user);
          $message->setContent($mess);
          $chat->addMessage($message);
          $dm->persist($chat);
          $help->setChat($chat);
        }

        $project->addSubscriber($user);


        $dm->persist($res);
        $dm->persist($project);
        $dm->flush();

        $from = $this->container->getParameter('mailer_sender_address');

        $message = \Swift_Message::newInstance()
          ->setSubject('The Science Tour : Nouvelle proposition d\'aide au projet ' . $project->getTitle())
          ->setFrom($from)
          ->setTo($project->getCreator()->getEmail())
          ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resHelpMail.txt.twig', array(
            'user'    => $user,
            'res'     => $res,
            'number'  => $number,
            'project' => $project
          )));
        if ($mess != NULL && $mess != "") {
          $project->getCreator()->addNotification("chat", $chat->getId());
          $dm->persist($project->getCreator());
          $dm->flush();
        }
        $this->get('mailer')->send($message);
        $project->getCreator()
          ->addNotification("project-resources", $project->getId());
        $dm->persist($project->getCreator());
        $dm->flush();

        return $this->render('TheScienceTourProjectBundle:resHelpers:resHelp.html.twig', array(
          'res'     => $res,
          'number'  => $number,
          'project' => $project
        ));
      }
      else {
        $error_message = "Le nombre d'" . $res->getName() . " doit être compris en 1 et " . ($res->getNumber() - $res->getActualNumber()) . ".";
      }

    }
    return $this->render('TheScienceTourProjectBundle:resHelpers:resHelpForm.html.twig', array(
      'res'           => $res,
      'project'       => $project,
      'error_message' => $error_message
    ));

  }

  public function resUnHelpAction($id, $idres) {
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
    if ($user == $project->getCreator()) {
      throw $this->createNotFoundException('Vous êtes le créateur du projet ' . $id);
    }

    $res = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Resource')
      ->find($idres);
    if (!$res) {
      throw $this->createNotFoundException('Aucune ressource trouvée avec l\'id ' . $idres);
    }
    if (!$project->getTools()->contains($res) && !$project->getMaterials()
        ->contains($res) && !$project->getPremises()->contains($res)
    ) {
      throw $this->createNotFoundException('La ressource ' . $idres . ' n\'appartient pas au projet ' . $id);
    }
    if (!$res->containsHelper($user)) {
      throw $this->createNotFoundException('Vous n\'aidez pas le projet avec cette compétence !');
    }
    if ($res->isHelpCompleted($user)) {
      throw $this->createNotFoundException('Impossible de supprimer une aide reçue !');
    }

    $dm   = $this->get('doctrine_mongodb')->getManager();
    $help = $res->getHelp($user);
    if ($help->getChat()) {
      foreach ($help->getChat()->getUsers() as $chatUser) {
        $chatUser->removeNotification("chat", $help->getChat()->getId());
        $dm->persist($chatUser);
      }
      $dm->remove($help->getChat());
    }
    $res->removeHelp($help);
    $project->setUpdatedAt(new \Datetime);
    $dm->persist($res);
    $dm->persist($project);
    $dm->flush();

    $from = $this->container->getParameter('mailer_sender_address');

    $message = \Swift_Message::newInstance()
      ->setSubject('The Science Tour : Annulation d\'aide au projet ' . $project->getTitle())
      ->setFrom($from)
      ->setTo($project->getCreator()->getEmail())
      ->setBody($this->renderView('TheScienceTourProjectBundle:resHelpers:resUnhelpMail.txt.twig', array(
        'user'    => $user,
        'res'     => $res,
        'project' => $project
      )));
    $this->get('mailer')->send($message);

    return $this->render('TheScienceTourProjectBundle:resHelpers:resUnhelp.html.twig', array(
      'res'     => $res,
      'project' => $project
    ));
  }

  public function resHelpShowAction($id, $idres) {

    $project = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Project')
      ->find($id);
    if (!$project || $project->getStatus() == 0) {
      throw $this->createNotFoundException('Aucun projet publié trouvé avec l\'id ' . $id);
    }

    $res = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Resource')
      ->find($idres);
    if (!$res) {
      throw $this->createNotFoundException('Aucune ressource trouvée avec l\'id ' . $idres);
    }
    if (!$project->getTools()->contains($res) && !$project->getMaterials()
        ->contains($res) && !$project->getPremises()->contains($res)
    ) {
      throw $this->createNotFoundException('La ressource ' . $idres . ' n\'appartient pas au projet ' . $id);
    }

    return $this->render('TheScienceTourProjectBundle:resHelpers:resHelpShow.html.twig', array(
      'project' => $project,
      'res'     => $res
    ));
  }

  /*
	 *  DELEGATE ACTIONS
	*/

  public function delegateAction($id) {
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

    $error_message = NULL;

    $request = $this->get('request');
    if ($request->getMethod() == 'POST') {
      $delegate_username = $request->request->get('delegate');
      if ($delegate_username == $user->getUsername()) {
        $error_message = "Vous ne pouvez pas vous passer la main à vous-même !";
      }
      else {
        $userManager = $this->get('fos_user.user_manager');
        $delegate    = $userManager->findUserByUsername($delegate_username);
        if (!$delegate) {
          $error_message = "Aucun utilisateur n'a été trouvé avec ce pseudo.";
        }
        else {
          $dm = $this->get('doctrine_mongodb')->getManager();
          $project->setDelegate($delegate);
          $dm->persist($project);
          $dm->flush();

          $from = $this->container->getParameter('mailer_sender_address');

          $message = \Swift_Message::newInstance()
            ->setSubject('The Science Tour : ' . $user->getUsername() . ' vous passe la main pour administrer le projet ' . $project->getTitle())
            ->setFrom($from)
            ->setTo($delegate->getEmail())
            ->setBody($this->renderView('TheScienceTourProjectBundle:delegate:delegateMail.txt.twig', array(
              'project'  => $project,
              'delegate' => $delegate
            )));
          $this->get('mailer')->send($message);

          $session = $this->get('session');
          $session->getFlashBag()
            ->add('notice', "Un e-mail vient d'être envoyé à " . $delegate->getUsername() . ". Vous resterez administrateur de ce projet jusqu'à ce qu'il accepte votre proposition.");

          return $this->redirect($this->generateUrl('tst_project_admin', array(
            'id'  => $id,
            'tab' => "team"
          )));
        }
      }
    }

    return $this->render('TheScienceTourProjectBundle:delegate:delegateForm.html.twig', array(
      'project'       => $project,
      'error_message' => $error_message
    ));

  }

  public function activateDelegateAction($id) {
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
    if ($user != $project->getDelegate()) {
      throw new AccessDeniedException();
    }

    $original = $project->getCreator();

    $dm = $this->get('doctrine_mongodb')->getManager();

    $project->setCreator($user);
    $project->setDelegate(NULL);

    $news = new News();
    $news->setProjectId($project->getId());
    $news->setAuthor($user);
    $news->setTitle("{% newAdmin %}");
    $dm->persist($news);
    foreach ($project->getEverybody() as $member) {
      if ($member != $user) {
        $member->addNotification("project-news", $project->getId());
        $dm->persist($member);
      }
    }

    $dm->persist($project);
    $dm->flush();

    $from = $this->container->getParameter('mailer_sender_address');

    $message = \Swift_Message::newInstance()
      ->setSubject('The Science Tour : Passer la main : proposition acceptée par ' . $user->getUsername())
      ->setFrom($from)
      ->setTo($original->getEmail())
      ->setBody($this->renderView('TheScienceTourProjectBundle:delegate:activateDelegateMail.txt.twig', array(
        'project'  => $project,
        'original' => $original
      )));
    $this->get('mailer')->send($message);

    return $this->render('TheScienceTourProjectBundle:delegate:activateDelegate.html.twig', array(
      'project' => $project
    ));
  }

  /*
	 *  CHATS ACTIONS
	*/

  public function addChatAction($id) {
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

    $privatizable = $project->getTeam()
        ->contains($user) || $this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD');

    $error_message = NULL;
    $mess          = "";

    $request = $this->get('request');
    if ($request->getMethod() == 'POST') {
      $mess = $request->request->get('message');

      $html_purifier = $this->container->get('exercise_html_purifier.twig_extension');
      $mess_nohtml   = $html_purifier->purify($mess, 'no_html');

      if ($mess == NULL || $mess_nohtml == "") {
        $error_message = "Tu dois entrer un message !";
      }
      elseif (strlen($mess_nohtml) > 5000) {
        $error_message = "Tu es trop bavard ! (5000 caractères maximum)";
      }
      else {
        $private = $request->request->get('private');
        if (!$private || !$privatizable) {
          $private = FALSE;
        }

        $dm = $this->get('doctrine_mongodb')->getManager();

        $mess_begin = strlen($mess_nohtml) > 25 ? mb_substr($mess_nohtml, 0, 25, 'utf-8') . '...' : $mess_nohtml;

        $chat = new Chat();
        $chat->addUser($user);
        $chat->setTitle($project->getTitle() . ' - ' . $mess_begin);
        $chat->setPrivate($private);

        $message = new Message();
        $message->setAuthor($user);
        $message->setContent($mess);

        $chat->addMessage($message);

        $project->addChat($chat);

        if ($user != $project->getCreator()) {
          $from = $this->container->getParameter('mailer_sender_address');
          $mail = \Swift_Message::newInstance()
            ->setSubject('The Science Tour : Nouvelle discussion dans votre projet ' . $project->getTitle())
            ->setFrom($from)
            ->setTo($project->getCreator()->getEmail())
            ->setBody($this->renderView('TheScienceTourProjectBundle:chat:addChatMail.txt.twig', array(
              'project' => $project,
              'chat'    => $chat,
              'author'  => $user
            )));

          $this->get('mailer')->send($mail);
          $project->getCreator()
            ->addNotification("project-chats", $project->getId());
          $dm->persist($project->getCreator());
        }

        $project->setUpdatedAt(new \Datetime);

        $dm->persist($chat);
        $dm->persist($project);
        $dm->flush();

        return $this->redirect($this->generateUrl('tst_project', array(
          'id'  => $id,
          'tab' => "chats"
        )));
      }

    }

    return $this->render('TheScienceTourProjectBundle:chat:addChat.html.twig', array(
      'project'       => $project,
      'privatizable'  => $privatizable,
      'error_message' => $error_message,
      'mess'          => $mess
    ));

  }

  public function addMessageAction($id, $idchat) {
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

    $chat = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourMessageBundle:Chat')
      ->find($idchat);

    if (!$chat) {
      throw $this->createNotFoundException('Aucune discussion trouvée avec l\'id ' . $idchat);
    }
    if (!$project->getChats()->contains($chat)) {
      throw $this->createNotFoundException('Cette discussion ne fait pas partie du projet');
    }
    if ($chat->getPrivate() && !$project->getTeam()
        ->contains($user) && !$this->get('security.context')
        ->isGranted('ROLE_PROJECT_MOD')
    ) {
      throw $this->createNotFoundException('Cette discussion est privée');
    }

    $error_message = NULL;
    $mess          = "";

    $request = $this->get('request');
    if ($request->getMethod() == 'POST') {
      $mess = $request->request->get('message');

      $html_purifier = $this->container->get('exercise_html_purifier.twig_extension');
      $mess_nohtml   = $html_purifier->purify($mess, 'no_html');

      if ($mess == NULL || $mess_nohtml == "") {
        $error_message = "Tu dois entrer un message !";
      }
      elseif (strlen($mess_nohtml) > 5000) {
        $error_message = "Tu es trop bavard ! (5000 caractères maximum)";
      }
      else {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $chat->addUser($user);

        $message = new Message();
        $message->setAuthor($user);
        $message->setContent($mess);

        $chat->addMessage($message);

        if (!($chat->getUsers()->count() == 1 && $chat->getUsers()
            ->first() == $user)
        ) {

          $from = $this->container->getParameter('mailer_sender_address');
          $mail = \Swift_Message::newInstance()
            ->setSubject('The Science Tour : Nouveau message - projet ' . $project->getTitle())
            ->setFrom($from)
            ->setTo($from)
            ->setBody($this->renderView('TheScienceTourProjectBundle:chat:addMessageMail.txt.twig', array(
              'project' => $project,
              'chat'    => $chat,
              'author'  => $user
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

        $project->setUpdatedAt(new \Datetime);

        $dm->persist($chat);
        $dm->persist($project);
        $dm->flush();

        return $this->redirect($this->generateUrl('tst_project', array(
          'id'  => $id,
          'tab' => "chats"
        )));
      }
    }

    return $this->render('TheScienceTourProjectBundle:chat:addMessage.html.twig', array(
      'project'       => $project,
      'chat'          => $chat,
      'error_message' => $error_message,
      'mess'          => $mess
    ));

  }

  public function deleteChatAction($id, $idchat) {
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

    $chat = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourMessageBundle:Chat')
      ->find($idchat);

    if (!$chat) {
      throw $this->createNotFoundException('Aucune discussion trouvée avec l\'id ' . $idchat);
    }
    if (!$project->getChats()->contains($chat)) {
      throw $this->createNotFoundException('Cette discussion ne fait pas partie du projet');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    foreach ($chat->getUsers() as $chatUser) {
      $chatUser->removeNotification("chat", $chat->getId());
      $dm->persist($chatUser);
    }
    $project->removeChat($chat);

    if (!($chat->getUsers()->count() == 1 && $chat->getUsers()
        ->first() == $user)
    ) {

      $from = $this->container->getParameter('mailer_sender_address');
      $mail = \Swift_Message::newInstance()
        ->setSubject('The Science Tour : Discussion supprimée - projet ' . $project->getTitle())
        ->setFrom($from)
        ->setTo($from)
        ->setBody($this->renderView('TheScienceTourProjectBundle:chat:deleteChatMail.txt.twig', array(
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
    $dm->persist($project);
    $dm->flush();
    return $this->redirect($this->generateUrl('tst_project', array(
      'id'  => $project->getId(),
      'tab' => "chats"
    )));

  }

  /*
	 * SPONSOR ACTIONS
	 */

  public function sponsorAction($id) {
    $user = $this->getUser();
    if (!$user || !$this->get('security.context')
        ->isGranted('ROLE_RESEARCHER')
    ) {
      throw new AccessDeniedException();
    }
    $project = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Project')
      ->find($id);
    if (!$project || $project->getStatus() == 0 || !$project->getChallenge()) {
      throw $this->createNotFoundException('Aucun projet-défi publié trouvé avec l\'id ' . $id);
    }
    if ($user == $project->getCreator()) {
      throw $this->createNotFoundException('Vous êtes le créateur du projet ' . $id);
    }
    if ($project->getSponsors()->contains($user)) {
      throw $this->createNotFoundException('Vous parrainez déjà ce projet !');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $project->addSponsor($user);
    $project->setUpdatedAt(new \Datetime);
    $dm->persist($project);

    $news = new News();
    $news->setProjectId($project->getId());
    $news->setAuthor($user);
    $news->setTitle("{% newSponsor %}");
    $dm->persist($news);
    foreach ($project->getEverybody() as $member) {
      if ($member != $user) {
        $member->addNotification("project-news", $project->getId());
        $dm->persist($member);
      }
    }

    $dm->flush();

    return $this->redirect($this->generateUrl('tst_project', array('id' => $id)));
  }

  public function unsponsorAction($id) {
    $user = $this->getUser();
    if (!$user || !$this->get('security.context')
        ->isGranted('ROLE_RESEARCHER')
    ) {
      throw new AccessDeniedException();
    }
    $project = $this->get('doctrine_mongodb')
      ->getRepository('TheScienceTourProjectBundle:Project')
      ->find($id);
    if (!$project || $project->getStatus() == 0 || !$project->getChallenge()) {
      throw $this->createNotFoundException('Aucun projet-défi publié trouvé avec l\'id ' . $id);
    }
    if ($user == $project->getCreator()) {
      throw $this->createNotFoundException('Vous êtes le créateur du projet ' . $id);
    }
    if (!$project->getSponsors()->contains($user)) {
      throw $this->createNotFoundException('Vous ne parrainez pas ce projet !');
    }

    $dm = $this->get('doctrine_mongodb')->getManager();
    $project->removeSponsor($user);
    $project->setUpdatedAt(new \Datetime);
    $dm->persist($project);
    $dm->flush();

    return $this->redirect($this->generateUrl('tst_project', array('id' => $id)));

  }
}
