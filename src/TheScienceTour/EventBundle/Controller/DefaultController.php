<?php

namespace TheScienceTour\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TheScienceTour\EventBundle\Document\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Geocoder\Geocoder;
use TheScienceTour\MainBundle\Model\GeoNear;

/**
 *
 * @author glouton aka Charles Rozier <charles.rozier@web2com.fr> <charles@guide2com.fr>
 *
 */
class DefaultController extends Controller
{

    /**
     *
     * @param string $filter
     * @param string $date
     * @return \Symfony\Component\HttpFoundation\Response
     */
	private function _agendaAction($filter, $center, $date = null)
    {
    	if ($form = $this->getRequest()->query->get('form', false)) {
    		if ($form['center']) {
    			if ($filter == "day") {
    				return $this->redirect($this->generateUrl('tst_agenda_day', array('date' => $date, 'center' => $form['center'])));
    			} else {
	    			return $this->redirect($this->generateUrl('tst_agenda', array('filter' => $filter, 'center' => $form['center'])));
    			}
    		}
    	}

    	$user = $this->getUser();
    	if ($filter == "favorite" && !$user) {
    		throw new AccessDeniedException();
    	}

    	// + Get Document Manager and repositories
    	// + --------------------------------------------------
    	$dm = $this->get('doctrine_mongodb')->getManager();
			/* @var $eventRepo \TheScienceTour\EventBundle\Repository\EventRepository */
    	$eventRepo = $dm->getRepository('TheScienceTourEventBundle:Event');

		//$user = $this->getUser();
    	// + --------------------------------------------------

    	// + Geocoders
    	// + --------------------------------------------------
    	$mapHelper = $this->get('the_science_tour_map.map_helper');

    	// + Fetch events
    	// + --------------------------------------------------
    	$geoNear = null;
    	$centerCoordinates = array();
    	$maxDistance = 50; // km
    	$userGeocode = null;

    	if ($center == 'around-me') {
	    	// For local testing put in your public IP.
	    	$userGeocode = $mapHelper->getGeocode($_SERVER['REMOTE_ADDR']);
			$geoNear = new GeoNear($userGeocode->getLatitude(), $userGeocode->getLongitude(), $maxDistance);
	    	$centerCoordinates = array(
	    			'latitude' => $userGeocode->getLatitude(),
	    			'longitude' => $userGeocode->getLongitude()
	    	);
		} elseif ($center && $center != 'all') {
			$geocode = $mapHelper->getGeocode($center);
			$geoNear = new GeoNear($geocode->getLatitude(), $geocode->getLongitude(), $maxDistance);
	    	$centerCoordinates = array(
	    			'latitude' => $geocode->getLatitude(),
	    			'longitude' => $geocode->getLongitude()
	    	);
		}

    	// Front page
    	$frontPageEvents = $eventRepo->findFrontPage($geoNear);
    	// Favorite
    	if (!$user) {
    		$userFavoriteEvents = array();
    		$favoriteEvents = new \ArrayObject();
    		$allFavoriteEvents = new \ArrayObject();
    	} else {
    		$userFavoriteEvents = $user->getFavoriteEvents();
    		$favoriteEvents = $eventRepo->findFavorite($userFavoriteEvents, $geoNear);
    		$allFavoriteEvents = $eventRepo->findFavorite($userFavoriteEvents);
    	}
    	// Past
    	$pastEvents = $eventRepo->findPast($geoNear);
    	// Next
    	$nextEvents = $eventRepo->findNext($geoNear);
    	// Trucks
    	$trucksEvents = $eventRepo->findTrucks($geoNear);

    	switch ($filter) {
    		case 'favorite':
    			$listTitle = 'Favorite events';
    			$eventList = $favoriteEvents;
    			$mapEventList = $allFavoriteEvents;
    			break;

    		case 'next':
    			$listTitle = 'The next events';
    			$eventList = $nextEvents;
    			$mapEventList = $eventRepo->findNext();
    			break;

    		case 'past':
    			$listTitle = 'The past events';
    			$eventList = $pastEvents;
    			$mapEventList = $eventRepo->findPast();
    			break;

    		case 'day':
    			$listTitle = $this->get('translator')->trans('Events for %day%', array('%day%' => $date
    			));
    			$eventList = $eventRepo->findDay($date, $geoNear);
    			$mapEventList = $eventRepo->findDay($date);
    			break;

    		case 'trucks':
    			$listTitle = 'Trucks';
    			$eventList = $trucksEvents;
    			$mapEventList = $eventRepo->findTrucks();
    			break;

    		default:
		    	$listTitle = 'Front page events';
		    	$eventList = $frontPageEvents;
		    	$mapEventList = $eventRepo->findFrontPage();
    	}
    	if ($filter == "day") {
    		$route = array('routeName' => 'tst_agenda_day', 'parameters' => array('filter' => $filter, 'date' => $date, 'center' => $center));
    	} else {
    		$route = array('routeName' => 'tst_agenda', 'parameters' => array('filter' => $filter, 'center' => $center));
    	}

    	// Map menus
    	$menus = array(
    			array(
    					'title' => $this->get('translator')->trans('Events'),
    					'before' => array(
    							'name' => 'TheScienceTourMapBundle:MapFilterGeoNear:default',
    							'params' => array('route' => $route),
    							'controller' => true
				    	),
    					'items' => array(
    							array (
    									'href' => $this->generateUrl(
    											'tst_agenda',
    											array('filter' => 'front-page', 'center' => $center)
    									),
    									'active' => ($filter == 'front-page'),
    									'icon' => 'icon-pushpin',
    									'text' => $this->get('translator')->trans('The top events'),
    									'details' => $frontPageEvents->count()
    							),
    							array (
    									'href' => $this->generateUrl(
    											'tst_agenda',
    											array('filter' => 'favorite', 'center' => $center)
    									),
    									'active' => ($filter == 'favorite'),
    									'icon' => 'icon-star',
    									'text' => $this->get('translator')->trans('Favorite events'),
    									'details' => $favoriteEvents->count()
    							),
    							array (
    									'href' => $this->generateUrl(
    											'tst_agenda',
    											array('filter' => 'next', 'center' => $center)
    									),
    									'active' => ($filter == 'next'),
    									'icon' => 'icon-forward',
    									'text' => $this->get('translator')->trans('The next events'),
    									'details' => $nextEvents->count()
    							),
    							array (
    									'href' => $this->generateUrl(
    											'tst_agenda',
    											array('filter' => 'past', 'center' => $center)
    									),
    									'active' => ($filter == 'past'),
    									'icon' => 'icon-backward',
    									'text' => $this->get('translator')->trans('The past events'),
    									'details' => $pastEvents->count()
    							),
    							array (
    									'href' => $this->generateUrl(
    											'tst_agenda',
    											array('filter' => 'trucks', 'center' => $center)
    									),
    									'active' => ($filter == 'trucks'),
    									'icon' => 'icon-truck',
    									'text' => $this->get('translator')->trans('Trucks'),
    									'details' => $trucksEvents->count()
    							)
    					)
    			)
    	);

    	if ($allFavoriteEvents && $allFavoriteEvents->count() > 0) {
	    	$asideMapTitle = 'My favorites';
    		$asideMapDocumentList = $allFavoriteEvents;
    	} else {
	    	$asideMapTitle = 'Around me';

    		if (!$userGeocode) {
		    	// For local testing put in your public IP.
		    	$userGeocode = $mapHelper->getGeocode($_SERVER['REMOTE_ADDR']);
	    	}

	    	$aroundMeGeoNear = new GeoNear($userGeocode->getLatitude(), $userGeocode->getLongitude(), $maxDistance);

    	    switch ($filter) {
	    		case 'next':
	    			$asideMapDocumentList = $eventRepo->findNext($aroundMeGeoNear);
	    			break;

	    		case 'past':
	    			$asideMapDocumentList = $eventRepo->findPast($aroundMeGeoNear);
	    			break;

	    		case 'day':
	    			$asideMapDocumentList = $eventRepo->findDay($date, $aroundMeGeoNear);
	    			break;

	    		case 'trucks':
	    			$asideMapDocumentList = $eventRepo->findTrucks($aroundMeGeoNear);
	    			break;

	    		default:
			    	$asideMapDocumentList = $eventRepo->findFrontPage($aroundMeGeoNear);
	    	}
    	}

		return $this->render('TheScienceTourEventBundle::agenda.html.twig', array(
				'mapEventList' => $mapEventList,
				'eventList' => $eventList,
				'listTitle' => $this->get('translator')->trans($listTitle),
				'route' => $route,
				'menus' => $menus,
				'centerCoordinates' => $centerCoordinates,
				'userFavoriteEvents' => $userFavoriteEvents,
				'asideMapTitle' => $asideMapTitle,
				'asideMapDocumentList' => $asideMapDocumentList
		));
    }

    /**
     * List event regarding the given filter
     * @param unknown $filter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function agendaAction($filter, $center) {
    	return $this->_agendaAction($filter, $center);
    }

    /**
     * List events which take place on the given day
     * @param string $date
     */
    public function dayAction($date, $center)
    {
    	return $this->_agendaAction('day', $center, $date);
    }

	/**
	 *
	 * @param string $id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function showAction($id) {
    	$user = $this->getUser();

    	if (!$user) {
    		$userFavoriteEvents = array();
    	} else {
    		$userFavoriteEvents = $user->getFavoriteEvents();
    	}


		$event = $this->get('doctrine_mongodb')
			->getRepository('TheScienceTourEventBundle:Event')
			->find($id);

	    if (!$event) {
        	throw $this->createNotFoundException('Aucun évènement trouvé avec l\'id '.$id);
    	}

    	return $this->render('TheScienceTourEventBundle::event.html.twig', array(
    			'event' => $event,
    			'userFavoriteEvents' => $userFavoriteEvents
    	));
	}

	public function favoritesAction($id, $action) {
		$user = $this->getUser();

		if (!$user) {
			throw new AccessDeniedException();
		}

		$event = $this->get('doctrine_mongodb')
		->getRepository('TheScienceTourEventBundle:Event')
		->find($id);

		if (!$event) {
			throw $this->createNotFoundException('Aucun évènement trouvé avec l\'id '.$id);
		}

		// Update and persist user's favorite events collection
		$dm = $this->get('doctrine_mongodb')->getManager();

		if ($action == 'add') {
			$flashMsg = 'The event has been added to your favorites';
			$user->addFavoriteEvent($event);
		} else {
			$flashMsg = 'The event has been removed from your favorites';
			$user->removeFavoriteEvent($event);
		}

		$dm->persist($user);
		$dm->flush();

		/** @var \Symfony\Component\HttpFoundation\Session $session  */
		$session = $this->get('session');

		$session->getFlashBag()->add('notice', $this->get('translator')->trans($flashMsg));

		return $this->redirect($this->generateUrl('tst_event', array('id' => $id)));
	}

}
