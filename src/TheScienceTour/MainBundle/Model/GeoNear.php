<?php
namespace TheScienceTour\MainBundle\Model;


use Doctrine\ODM\MongoDB\Query\Builder;
class GeoNear {
	private $latitude;
	private $longitude;
	private $maxDistance;
	
	function __construct($latitude = 0, $longitude = 0, $maxDistance = 50) {
		$this->latitude = $latitude;
		$this->longitude = $longitude;
		$this->maxDistance = $maxDistance;
	}
	
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}
	
	public function getLatitude() {
		return $this->latitude;
	}
	
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}
	
	public function getLongitude() {
		return $this->longitude;
	}
	
	public function setMaxDistance($maxDistance) {
		$this->maxDistance = $maxDistance;
	}
	
	public function getMaxDistance() {
		return $this->maxDistance;
	}
	
	public function addToQueryBuilder(Builder $qb) {
		// Convert from/to radians with 6378.137 for km and 3963.192 miles
		$earthRadius = 6378.137;
		$maxDistance = $this->maxDistance / $earthRadius;
	
		return $qb->field('coordinates')->geoNear($this->longitude, $this->latitude)
		->maxDistance($maxDistance)
		->spherical(true)
		->distanceMultiplier($earthRadius);
	}
}