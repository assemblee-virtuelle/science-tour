<?php
namespace TheScienceTour\MainBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 *
 * @author glouton aka Charles Rozier <charles.rozier@web2com.fr> <charles@guide2com.fr>
 *
 */
class TheScienceTourRequestListener {
  private $router;
  private $earsmusDomains;

  public function __construct(Router $router, $earsmusDomains) {
    $this->router         = $router;
    $this->earsmusDomains = $earsmusDomains;
  }

  public function onKernelRequest(GetResponseEvent $event) {
    // We are on the Erasmus website.
    if (in_array($_SERVER['HTTP_HOST'], $this->earsmusDomains)) {
      /** @var \Symfony\Component\HttpFoundation\Request $request */
      $request = $event->getRequest();
      /** @var \Symfony\Component\HttpFoundation\Session $session */
      $session = $request->getSession();
      // Save for further usage.
      $session->set('isErasmus', TRUE);
    }
  }
}
