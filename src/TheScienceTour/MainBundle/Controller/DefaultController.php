<?php

namespace TheScienceTour\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    public function indexAction()
    {
	$manager = new \MongoDB\Driver\Manager("mongodb://localhost:27017");
	$query = new \MongoDB\Driver\Query([], []);

	$readPreference = new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_PRIMARY);
	$cursor = $manager->executeQuery('science-tour.Project', $query, $readPreference);

foreach($cursor as $document) {
    var_dump($document);
}

die;
        return $this->render('TheScienceTourMainBundle:Default:index.html.twig');
    }
}
