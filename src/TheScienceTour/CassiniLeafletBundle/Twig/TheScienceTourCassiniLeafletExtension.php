<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 03/10/2017
 * Time: 18:44
 */

namespace TheScienceTour\CassiniLeafletBundle\Twig;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerInterface;


class TheScienceTourCassiniLeafletExtension extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(new \Twig_SimpleFunction('defaultGeolocation', array($this, 'defaultGeolocationFunction')));
    }

    public function defaultGeolocationFunction()
    {
        $latd = $this->container->getParameter('cassini_leaflet_latitude');
        $long = $this->container->getParameter('cassini_leaflet_longitude');
        $zoom = $this->container->getParameter('cassini_leaflet_zoom');

        return "const DEF_LATITUDE = $latd, DEF_LONGITUDE = $long, DEF_ZOOM = $zoom;";
    }

    public function getName()
    {
        return 'cassini_extension';
    }
}