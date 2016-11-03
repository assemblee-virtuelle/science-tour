<?php

namespace TheScienceTour\MapBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Coordinates
{
	/**
	 * @MongoDB\Float
	 */
	protected $longitude;

	/**
	 * @MongoDB\Float
	 */
	protected $latitude;

	/**
	 * Set longitude
	 *
	 * @param float $longitude
	 * @return self
	 */
	public function setLongitude($longitude)
	{
		$this->longitude = $longitude;
		return $this;
	}

	/**
	 * Get longitude
	 *
	 * @return float $longitude
	 */
	public function getLongitude()
	{
		return $this->longitude;
	}

	/**
	 * Set latitude
	 *
	 * @param float $latitude
	 * @return self
	 */
	public function setLatitude($latitude)
	{
		$this->latitude = $latitude;
		return $this;
	}

	/**
	 * Get latitude
	 *
	 * @return float $latitude
	 */
	public function getLatitude()
	{
		return $this->latitude;
	}
	
	public function __toString() {
		return $this->latitude.", ".$this->longitude;
	}
}
