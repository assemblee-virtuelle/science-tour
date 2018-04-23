<?php

namespace TheScienceTour\PeerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('PeerBundle:Default:index.html.twig');
    }
}
