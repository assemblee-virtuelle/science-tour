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
	
	public function __construct(Router $router) {
		$this->router = $router;
	}
	
	public function onKernelRequest(GetResponseEvent $event) {
		if ($event->getRequestType() !== \Symfony\Component\HttpKernel\HttpKernel::MASTER_REQUEST) {
			return;
		}
		
		/** @var \Symfony\Component\HttpFoundation\Request $request  */
		$request = $event->getRequest();
		var_dump($request->attributes->get('_route')); exit;
		
		// Do not save target path for XHR and non-GET requests
		// You can add any more logic here you want
		if ($request->isXmlHttpRequest() || 'GET' !== $request->getMethod()) {
			return;
		}
		
		/** @var \Symfony\Component\HttpFoundation\Session $session  */
		$session = $request->getSession();
	
		$routeParams = $this->router->match($request->getPathInfo());
		$routeName = $routeParams['_route'];
		if ($routeName[0] == '_') {
			return;
		}
		unset($routeParams['_route']);
		$routeData = array('name' => $routeName, 'params' => $routeParams);
	
		// Skipping duplicates
		$thisRoute = $session->get('this_route', array());
		if ($thisRoute == $routeData) {
			return;
		}
		
		// Skipping FOSJsRouting
		if($thisRoute['name'] == 'fos_js_routing_js') {
			return;
		}
		
		$session->set('last_route', $thisRoute);
		$session->set('this_route', $routeData);
	}
}
