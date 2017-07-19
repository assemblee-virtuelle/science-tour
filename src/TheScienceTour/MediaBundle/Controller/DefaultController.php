<?php

namespace TheScienceTour\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('TheScienceTourMediaBundle:Default:index.html.twig');
    }
}
