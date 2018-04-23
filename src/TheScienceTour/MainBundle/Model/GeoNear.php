<?php
namespace TheScienceTour\MainBundle\Model;


use Doctrine\ODM\MongoDB\Query\Builder;

/**
 * Class GeoNear
 *
 * Classe permettant de définir un périmètre d'un certain rayon, autour de coordonnées géolocalisées
 * de amnière à définir la notion de proximité, utile dans certains cas de recherche
 *
 * @package TheScienceTour\MainBundle\Model
 */
class GeoNear {
    /**
     * Latitude
     *
     * @var float
     */
	private $latitude;

    /**
     * Longitude
     *
     * @var float
     */
	private $longitude;

    /**
     * Distance maximale autour du point de référence
     * @var float
     */
	private $maxDistance;

    /**
     * Constructeur de GeoNear.
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $maxDistance
     * @return GeoNear
     */
	function __construct(float $latitude = 0, float $longitude = 0, float $maxDistance = 50) {
		$this->latitude = $latitude;
		$this->longitude = $longitude;
		$this->maxDistance = $maxDistance;

		return $this;
	}

    /**
     * Mutateur de la propriété $latitude
     *
     * @param float $latitude
     * @return GeoNear
     */
	public function setLatitude(float $latitude) {
		$this->latitude = $latitude;

		return $this;
	}

    /**
     * Accesseur de la propriété $latitude
     *
     * @return float
     */
	public function getLatitude() {
		return $this->latitude;
	}

    /**
     * Mutateur de la propriété $longitude
     *
     * @param float $longitude
     * @return GeoNear
     */
	public function setLongitude(float $longitude) {
		$this->longitude = $longitude;

		return $this;
	}

    /**
     * Accesseur de la propriété $longitude
     *
     * @return float
     */
	public function getLongitude() {
		return $this->longitude;
	}

    /**
     * Mutateur de la propriété $maxDistance
     *
     * @param float $maxDistance
     * @return GeoNear
     */
	public function setMaxDistance(float $maxDistance) {
		$this->maxDistance = $maxDistance;

		return $this;
	}

    /**
     * Accesseur de la propriété $maxDistance
     *
     * @return float
     */
	public function getMaxDistance() {
		return $this->maxDistance;
	}

    /**
     * Restreint la recherche à un périmètre géographique
     *
     * Si l'on précise que la recherche de Projets doit se faire à proximité d'un lieu donné (géolocalisé),
     * cette méthode ajoute les contraintes supplémentaires nécessaires à la restriction du périmètre géographique de la recherche
     *
     * @param Builder $qb
     * @return mixed
     */
	public function addToQueryBuilder(Builder $qb) {
		// Convert from/to radians with 6378.137 for km and 3963.192 miles
		$earthRadius = 6378.137;
		$maxDistance = $this->maxDistance / $earthRadius;
	
		return $qb->field('coordinates')
                  ->geoNear($this->longitude, $this->latitude)
                  ->maxDistance($maxDistance)
		          ->spherical(true)
		          ->distanceMultiplier($earthRadius);
	}
}