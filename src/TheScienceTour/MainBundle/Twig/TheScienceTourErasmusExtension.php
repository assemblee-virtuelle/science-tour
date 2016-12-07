<?php

namespace TheScienceTour\MainBundle\Twig;

use Symfony\Component\HttpFoundation\Session\Session;

class TheScienceTourErasmusExtension extends \Twig_Extension {
  private $session;

  function __construct(Session $session) {
    $this->session = $session;
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('isErasmus', array($this, 'isErasmusFunction')),
    );
  }

  public function getName() {
    return 'erasmus';
  }

  public function isErasmusFunction() {
    return $this->session->get('isErasmus', false);
  }
}
