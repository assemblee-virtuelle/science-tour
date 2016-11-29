<?php
namespace TheScienceTour\MainBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
    if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
      // don't do anything if it's not the master request
      return;
    }
    static $isErasmus = NULL;
    // Prevent multiple calls.
    if ($isErasmus === NULL) {
      /** @var \Symfony\Component\HttpFoundation\Request $request */
      $request = $event->getRequest();
      // We are on the Erasmus website.
      if (in_array($request->getHttpHost(), $this->earsmusDomains)) {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        // Save for further usage.
        $session->set('isErasmus', TRUE);
        $isErasmus = TRUE;
      }
      $isErasmus = FALSE;
    }
  }
}
