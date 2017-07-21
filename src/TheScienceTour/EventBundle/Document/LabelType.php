<?php

namespace TheScienceTour\EventBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;

/**
 * @MongoDB\Document(requireIndexes=true)
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"}, options={"unique"=true}),
 *   @MongoDB\Index(keys={"slug"="asc"}, options={"unique"=true})
 * })
 */
class LabelType {
	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Field(type="String")
	 * @Assert\NotBlank()
	 */
	protected $name;

	/**
	 * @MongoDB\Field(type="String")
	 * @Assert\NotBlank()
	 */
	protected $slug;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist"})
	 * @TSTMediaAssert\ValidImgRes()
	 * @TSTMediaAssert\ValidImgSize()
	 */
	protected $marker;

	/**
	 * @MongoDB\Field(type="String")
	 * @Assert\NotBlank()
	 */
	protected $markerFormat;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist"})
	 * @TSTMediaAssert\ValidImgRes()
	 * @TSTMediaAssert\ValidImgSize()
	 */
	protected $picture; // Used as the default Event.picture if none has been set

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
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return self
     */
    public function setSlug($slug)
    {
    	$this->slug = $slug;
    	return $this;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
    	return $this->slug;
    }

    /**
     * Set marker
     *
     * @param TheScienceTour\MediaBundle\Document\Media $marker
     * @return self
     */
    public function setMarker(\TheScienceTour\MediaBundle\Document\Media $marker)
    {
    	$this->marker = $marker;
    	return $this;
    }

    /**
     * Get marker
     *
     * @return TheScienceTour\MediaBundle\Document\Media $marker
     */
    public function getMarker()
    {
    	return $this->marker;
    }

    /**
     * Set markerFormat
     *
     * @param string $markerFormat
     * @return self
     */
    public function setMarkerFormat($markerFormat)
    {
        $this->markerFormat = $markerFormat;
        return $this;
    }

    /**
     * Get markerFormat
     *
     * @return string $markerFormat
     */
    public function getMarkerFormat()
    {
        return $this->markerFormat;
    }

    /**
     * Set picture
     *
     * @param TheScienceTour\MediaBundle\Document\Media $picture
     * @return self
     */
    public function setPicture(\TheScienceTour\MediaBundle\Document\Media $picture)
    {
    	$this->picture = $picture;
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

    public function __toString() {
    	return $this->name;
    }
}
