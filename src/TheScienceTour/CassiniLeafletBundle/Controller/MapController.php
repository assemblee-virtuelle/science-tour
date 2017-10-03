<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 02/10/2017
 * Time: 13:57
 */

namespace TheScienceTour\CassiniLeafletBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MapController extends Controller
{

    /**
     * mapAction affiche une carte géographique agrémentée de marqueurs de position
     *
     * @param array $documentList Liste d'objets à placer (projets, camions, etc.)
     * @param array $route Liste de routes
     * @param array $menus Liste de menus
     * @param array $centerCoordinates Coordonnées géographiques du centre de la carte
     * @param bool $defaultMap Doit-on afficher la carte par défaut ?
     *
     * @return Response
     */
    public function mapAction(
        array $documentList = [],
        array $route = [],
        array $menus = [],
        array $centerCoordinates = [],
        bool $defaultMap = false
    ) : Response
    {
        if ($this->get('kernel')->getEnvironment() === 'dev') {
            return new Response('Les cartes sont désactivées en environnement de dev.');
        }

        return $this->render('TheScienceTourMapBundle::map.html.twig', array(
            'route' => $route,
            'menus' => $menus,
        ));
    }

}

