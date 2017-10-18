<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 16/10/2017
 * Time: 17:09
 */

namespace TheScienceTour\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Ivory\GoogleMap\Places\AutocompleteType;

use TheScienceTour\MainBundle\Model\GeoNear;

/**
 * Class JsonController
 * @package TheScienceTour\ProjectBundle\Controller
 */
class JsonController extends Controller
{
    const MAX_DISTANCE = 40;

    /**
     * Liste des positions géolocalisées des projets en cours, sours forme d'un objet JSON
     *
     * @param Request $request
     * @param  float latitude
     * @param  float longitude
     * @return JsonResponse
     */
    public function inProgressPositionsAction(Request $request, float $latitude, float $longitude) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $geoNear = new GeoNear($latitude, $longitude, self::MAX_DISTANCE);

        $inProgressProjects = $dm->getRepository('TheScienceTourProjectBundle:Project')->findInProgress($geoNear)->execute();

        $positions = [];
        foreach ($inProgressProjects as $project) {
            $positions[] = ['longitude' => $project.coordinates.longitude, 'latitude' => $project.coordinates.latitude];
        }

        return new JsonResponse($positions);
    }

    /**
     * Liste des positions géolocalisées des projets terminés, sours forme d'un objet JSON
     *
     * @param Request $request
     * @param  float $latitude
     * @param  float $longitude
     * @return JsonResponse
     */
    public function finishedPositionsAction(Request $request, float $latitude, float $longitude) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $geoNear = new GeoNear($latitude, $longitude, self::MAX_DISTANCE);

        $inProgressProjects = $dm->getRepository('TheScienceTourProjectBundle:Project')->findFinished($geoNear)->execute();

        $positions = [];
        foreach ($inProgressProjects as $project) {
            $positions[] = ['longitude' => $project.coordinates.longitude, 'latitude' => $project.coordinates.latitude];
        }

        return new JsonResponse($positions);
    }

    /**
     * Liste des positions géolocalisées des projets bientôt terminés, sours forme d'un objet JSON
     *
     * @param Request $request
     * @param  float $latitude
     * @param  float $longitude
     * @return JsonResponse
     */
    public function finishedSoonPositionsAction(Request $request, float $latitude, float $longitude) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $geoNear = new GeoNear($latitude, $longitude, self::MAX_DISTANCE);

        $inProgressProjects = $dm->getRepository('TheScienceTourProjectBundle:Project')->findFinishedSoon($geoNear)->execute();

        $positions = [];
        foreach ($inProgressProjects as $project) {
            $positions[] = ['longitude' => $project.coordinates.longitude, 'latitude' => $project.coordinates.latitude];
        }

        return new JsonResponse($positions);
    }

    /**
     * Liste des positions géolocalisées des pojets mis à jour récemment, sours forme d'un objet JSON
     *
     * @param Request $request
     * @param  float $latitude
     * @param  float $longitude
     * @return JsonResponse
     */
    public function lastUpdatedPositionsAction(Request $request, float $latitude, float $longitude) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $geoNear = new GeoNear($latitude, $longitude, self::MAX_DISTANCE);

        $inProgressProjects = $dm->getRepository('TheScienceTourProjectBundle:Project')->findLastUpdated($geoNear)->execute();

        $positions = [];
        foreach ($inProgressProjects as $project) {
            $positions[] = ['longitude' => $project.coordinates.longitude, 'latitude' => $project.coordinates.latitude];
        }

        return new JsonResponse($positions);
    }
}