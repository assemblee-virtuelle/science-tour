<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 25/10/2017
 * Time: 15:17
 */

namespace TheScienceTour\EventBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class MapJsonController
 * @package TheScienceTour\EventBundle\Controller
 */
class JsonController extends Controller
{

    public function markersRender (
        Request $request,
        string $contentPattern,
        string $filter,
        string $latitude,
        string $longitude,
        string $around) : Response
    {
        $user = $this->getUser();
        if ($filter == "favorite" && !$user) {
            throw new AccessDeniedException();
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        $eventRepo = $dm->getRepository('TheScienceTourEventBundle:Event');

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

        return $this->render('TheScienceTourEventBundle::agenda.html.twig', [
            'listTitle' => $this->get('translator')->trans($listTitle),
            'eventList' => $eventList,
            'userFavoriteEvents' => $userFavoriteEvents,
            'menus' => $menus,
        ]);

    }


}