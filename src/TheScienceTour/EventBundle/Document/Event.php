<?php

namespace TheScienceTour\EventBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use TheScienceTour\MapBundle\Validator\Constraints as TSTMapAssert;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;

/**
 * @MongoDB\Document(repositoryClass="TheScienceTour\EventBundle\Repository\EventRepository", requireIndexes=true)
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"bidullActivityId"="asc"}, options={"sparse"=true, "unique"=true}),
 *   @MongoDB\Index(keys={"label.$id"="asc"}),
 *   @MongoDB\Index(keys={"startDate"="asc"}),
 *   @MongoDB\Index(keys={"endDate"="asc"}),
 *   @MongoDB\Index(keys={"frontPage"="desc"}),
 *   @MongoDB\Index(keys={"coordinates"="2d"})
 * })
 */
class Event {
	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Field(type="String")
	 * @Assert\NotBlank()
	 */
	protected $title;

	/**
	 * @MongoDB\Field(type="Int")
	 */
	protected $bidullActivityId;

	/**
	 * @MongoDB\Field(type="String")
	 * @Assert\NotBlank()
	 */
	protected $description;

	/**
	 * @MongoDB\Date
	 * @Assert\NotBlank()
	 */
	protected $startDate;

	/**
	 * @MongoDB\Date
	 * @Assert\NotBlank()
	 */
	protected $endDate;

	/**
	 * @MongoDB\Field(type="String")
	 * @Assert\NotBlank()
	 * @TSTMapAssert\ProvidedAddress()
	 */
	protected $place;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist", "remove"})
	 * @TSTMediaAssert\ValidImgRes()
	 * @TSTMediaAssert\ValidImgSize()
	 */
	protected $picture;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\EventBundle\Document\Label")
	 */
	protected $label;

	/**
	 * @MongoDB\Field(type="Bool")ean
	 */
	protected $frontPage;

	/**
	 * @MongoDB\EmbedOne(targetDocument="TheScienceTour\MapBundle\Document\Coordinates")
	 */
	protected $coordinates;

	/**
	 * @MongoDB\Distance
	 */
	protected $distance;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set bidullActivityId
     *
     * @param int $bidullActivityId
     * @return self
     */
    public function setBidullActivityId($bidullActivityId)
    {
    	$this->bidullActivityId = $bidullActivityId;
    	return $this;
    }

    /**
     * Get bidullActivityId
     *
     * @return int $bidullActivityId
     */
    public function getBidullActivityId()
    {
    	return $this->bidullActivityId;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set startDate
     *
     * @param date $startDate
     * @return self
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * Get startDate
     *
     * @return date $startDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param date $endDate
     * @return self
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Get endDate
     *
     * @return date $endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set place
     *
     * @param string $place
     * @return self
     */
    public function setPlace($place)
    {
        $this->place = $place;
        return $this;
    }

    /**
     * Get place
     *
     * @return string $place
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set picture
     *
     * @param TheScienceTour\MediaBundle\Document\Media $picture
     * @return self
     */
    public function setPicture($picture)
    {
    	if ($picture) {
	        $this->picture = $picture;
	        $this->picture->setName($this->title);
    	} else {
    		$this->picture = null;
    	}
        return $this;
    }

    /**
     * Get picture
     *
     * @return TheScienceTour\MediaBundle\Document\Media $picture
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set frontPage
     *
     * @param boolean $frontPage
     * @return self
     */
    public function setFrontPage($frontPage)
    {
        $this->frontPage = $frontPage;
        return $this;
    }

    /**
     * Get frontPage
     *
     * @return boolean $frontPage
     */
    public function getFrontPage()
    {
        return $this->frontPage;
    }

    /**
     * Set coordinates
     *
     * @param TheScienceTour\MapBundle\Document\Coordinates $coordinates
     * @return self
     */
    public function setCoordinates(\TheScienceTour\MapBundle\Document\Coordinates $coordinates)
    {
        $this->coordinates = $coordinates;
        return $this;
    }

    /**
     * Get coordinates
     *
     * @return TheScienceTour\MapBundle\Document\Coordinates $coordinates
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    public function unsetCoordinates()
    {
    	unset($this->coordinates);
    }

    /**
     * Set distance
     *
     * @param string $distance
     * @return self
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * Get distance
     *
     * @return string $distance
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set label
     *
     * @param TheScienceTour\EventBundle\Document\Label $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get label
     *
     * @return TheScienceTour\EventBundle\Document\Label $label
     */
    public function getLabel()
    {
        return $this->label;
    }
}
