<?php
 
namespace TheScienceTour\MainBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Common\Collections\ArrayCollection;

class MainController extends Controller {
	
	public function homeAction() {
		// Ensure indexes
		// TODO: Find a better place to run this
		$dm = $this->get('doctrine_mongodb')->getManager();
		$dm->getSchemaManager()->ensureIndexes();
		
    	$projectRepo = $dm->getRepository('TheScienceTourProjectBundle:Project');
		
		// Projects sticked on the front page
		$projectListQuery = $projectRepo->findFrontPage();
		$projectList = $projectListQuery->execute();
		
		// Projects around me
		$maxDistance = 50; // km
		$mapHelper = $this->get('the_science_tour_map.map_helper');
		$aroundMeProjects = null;
		
		$trucksList = $dm->getRepository('TheScienceTourEventBundle:Event')->findTrucks();
		
		try {
			$userGeocode = $mapHelper->getGeocode($_SERVER['REMOTE_ADDR']);
			
			$aroundMeProjectsQuery = $projectRepo->findGeoNear($userGeocode->getLatitude(), $userGeocode->getLongitude(), $maxDistance);
			$aroundMeProjects = $aroundMeProjectsQuery->execute();
		} catch (Exception $e) {
			$session = $this->get('session');
			$session->getFlashBag()->add('notice', $e->getMessage());
		}
		
		return $this->render('TheScienceTourMainBundle::home.html.twig', array(
				'projectList' => $projectList,
				'aroundMeProjects' => $aroundMeProjects,
				'trucksList' => $trucksList
		));
	}
	
	public function searchAction($request) {
		
		$finder = $this->container->get('fos_elastica.finder.tst.project');
		$query = new \Elastica\Query\QueryString($request);
		$term = new \Elastica\Filter\Term(array('status' => 1));
		$filteredQuery = new \Elastica\Query\Filtered($query, $term);
		$result = $finder->find($filteredQuery);	
	
		return $this->render('TheScienceTourMainBundle::search.html.twig', array(
				'request' => urldecode($request),
				'result' => $result
		));
	}

}
