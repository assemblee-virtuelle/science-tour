<?php

namespace TheScienceTour\EventBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use TheScienceTour\ContentPatternBundle\Model\ContentInterface;
use TheScienceTour\MapBundle\Validator\Constraints as TSTMapAssert;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;
use TheScienceTour\ContentPatternBundle\Model\Content;

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
class Event extends Content {
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
	 * @MongoDB\Field(type="int")
	 */
	protected $bidullActivityId;

	/**
	 * @MongoDB\Field(type="string")
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
	 * @MongoDB\Field(type="string")
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
	 * @MongoDB\Field(type="bool")
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
     * Get bidullActivityId
     *
     * @return int $bidullActivityId
     */
    public function getBidullActivityId() : int
    {
        return $this->bidullActivityId;
    }


    /**
     * Set bidullActivityId
     *
     * @param int $bidullActivityId
     * @return ContentInterface
     */
    public function setBidullActivityId(int $bidullActivityId) : self
    {
    	$this->bidullActivityId = $bidullActivityId;
    	return $this;
    }

    /**
     * Alias de la méthode setStartedAt
     *
     * @param \DateTime $startDate
     * @return ContentInterface
     */
    public function setStartDate(\DateTime $startDate) :self
    {
        return $this->setStartedAt($startDate);
    }

    /**
     * Alias de la méthode getStartedAt
     *
     * @return \DateTime $startDate
     */
    public function getStartDate() : \Datetime
    {
        return (($legacy && is_null($this->startedAt)) ? $this->startDate : $this->startedAt);
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return ContentInterface
     */
    public function setEndDate($endDate) : self
    {
        return $this->setFinishedAt($endDate);
    }

    /**
     * Alias de la méthode getFinishedAt
     *
     * @return \DateTime $endDate
     */
    public function getEndDate() : \DateTime
    {
        return $this->getFinishedAt();
    }

    /**
     * Set label
     *
     * @param \TheScienceTour\EventBundle\Document\Label $label
     * @return self
     */
    public function setLabel(Label $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get label
     *
     * @return \TheScienceTour\EventBundle\Document\Label $label
     */
    public function getLabel() : Label
    {
        return $this->label;
    }
}
