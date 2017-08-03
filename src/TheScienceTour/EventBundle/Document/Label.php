<?php

namespace TheScienceTour\EventBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\Document(requireIndexes=true)
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"title"="asc"}),
 *   @MongoDB\Index(keys={"labelType.$id"="asc"})
 * })
 */
class Label {
	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Field(type="string")
	 * @Assert\NotBlank()
	 */
	protected $title;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\EventBundle\Document\LabelType")
	 */
	protected $labelType;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $backgroundColor;

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
     * Set backgroundColor
     *
     * @param string $backgroundColor
     * @return self
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;
        return $this;
    }

    /**
     * Get backgroundColor
     *
     * @return string $backgroundColor
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Set labelType
     *
     * @param TheScienceTour\EventBundle\Document\LabelType $labelType
     * @return self
     */
    public function setLabelType($labelType)
    {
    	$this->labelType = $labelType;
    	return $this;
    }

    /**
     * Get labelType
     *
     * @return TheScienceTour\EventBundle\Document\LabelType $labelType
     */
    public function getLabelType()
    {
    	return $this->labelType;
    }

    public function __toString() {
    	return $this->getTitle();
    }
}
