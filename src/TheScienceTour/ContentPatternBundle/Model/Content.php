<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 14/09/2017
 * Time: 16:43
 */

namespace TheScienceTour\ContentPatternBundle\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use TheScienceTour\MapBundle\Document\Coordinates;
use TheScienceTour\UserBundle\Document\User;
use TheScienceTour\MediaBundle\Document\Media;

/**
 * Class Content
 * @package TheScienceTour\ContentPatternBundle\Model
 *
 * @author Michel Cadennes <michel.cadennes@sens-commun.fr>
 *
 */
abstract class Content implements ContentInterface
{

    /**
     * PROPRIETES
     */

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(min="0", max="100")
     */
    protected $title;

    /**
     * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\UserBundle\Document\User", cascade={"persist", "remove"})
     * @Assert\NotBlank()
     */
    protected $creator;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank(groups={"publish"})
     */
    protected $description; // Rules of the game

    /**
     * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist", "remove"})
     * @Assert\NotNull()
     * @TSTMediaAssert\ValidImgRes()
     * @TSTMediaAssert\ValidImgSize()
     */
    protected $picture;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable
     * @MongoDB\Date
     */
    protected $updatedAt;

    /**
     * @MongoDB\Date
     */
    protected $publishedAt;

    /**
     * @MongoDB\Date
     */
    protected $startedAt;

    /**
     * @MongoDB\Date
     */
    protected $finishedAt;

    /**
     * @MongoDB\Field(type="string")
     * @TSTMapAssert\ProvidedAddress(groups={"publish"})
     */
    protected $place;

    /**
     * @MongoDB\EmbedOne(targetDocument="TheScienceTour\MapBundle\Document\Coordinates")
     */
    protected $coordinates;

    /**
     * @MongoDB\Distance
     */
    protected $distance;

    /**
     * @MongoDB\Field(type="int")
     * @Assert\Type(type="integer", groups={"publish"})
     * @Assert\NotBlank(groups={"publish"})
     * @Assert\Range(min=0, groups={"publish"})
     */
    protected $duration;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank(groups={"publish"})
     * @Assert\Choice({"day", "week", "month"})
     */
    protected $durationUnit;

    /**
     * @MongoDB\Field(type="bool")
     */
    protected $frontPage;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $language; // La langue du document

    /**
     * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\ContentPatternBundle\Model\Content", inversedBy="translations")
     */
    protected $principal; // Le document original

    /**
     * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\ContentPatternBundle\Model\Content", mappedBy="principal")
     */
    protected $translations; // Ensemble des traductions



    /**
     * CONSTRUCTEUR
     */

    /**
     * Content constructor.
     */
    public function __construct() {
        $this->frontPage = false;
    }


    /**
     * METHODES
     */

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}

     * @param int $id
     * @return ContentInterface
     */
    public function setId(int $id) :  ContentInterface
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return \TheScienceTour\UserBundle\Document\User
     */
    public function getCreator() : User
    {
        return $this->creator;
    }

    /**
     * @return mixed
     */
    public function setCreator(User $creator) :  ContentInterface
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * {@inheritdoc}
     *
     * @param string $title
     * @return ContentInterface
     */
    public function setTitle(string $title) :  ContentInterface
    {
        $this->title = $title;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}

     * @param string $description
     * @return ContentInterface
     */
    public function setDescription(string $description) :  ContentInterface
    {
        $this->description = $description;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return Media
     */
    public function getPicture() : Media
    {
        return $this->picture;
    }

    /**
     * {@inheritdoc}

     * @param \TheScienceTour\MediaBundle\Document\Media $picture
     * @return ContentInterface
     */
    public function setPicture(Media $picture) :  ContentInterface
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTime
     */
    public function getCreatedAt() : \Datetime
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTime $createdAt
     * @return ContentInterface
     */
    public function setCreatedAt(\DateTime $createdAt) :  ContentInterface
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTime
     */
    public function getUpdatedAt() : \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTime $updatedAt
     * @return ContentInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt) :  ContentInterface
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTime
     */
    public function getPublishedAt() : \DateTime
    {
        return $this->publishedAt;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTime $publishedAt
     * @return ContentInterface
     */
    public function setPublishedAt(\DateTime $publishedAt) :  ContentInterface
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
      * @return \DateTime $startDate
     */
    public function getStartedAt() : \DateTime
    {
        return $this->startedAt;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTime $startDate
     * @return ContentInterface
     */
    public function setStartedAt(\DateTime $startDate) :  ContentInterface
    {
        $this->startedAt = $startDate;
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime $endDate
     */
    public function getFinishedAt() : \DateTime
    {
        return $this->finishedAt;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTime $endDate
     * @return ContentInterface
     */
    public function setFinishedAt(\DateTime $endDate) :  ContentInterface
    {
        $this->finishedAt = $endDate;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPlace() : string
    {
        return $this->place;
    }

    /**
     * {@inheritdoc}

     * @param string $place
     * @return ContentInterface
     */
    public function setPlace($place) :  ContentInterface
    {
        $this->place = $place;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return \TheScienceTour\MapBundle\Document\Coordinates $coordinates
     */
    public function getCoordinates() : Coordinates
    {
        return $this->coordinates;
    }

    /**
     * {@inheritdoc}
     *
     * @param \TheScienceTour\MapBundle\Document\Coordinates $coordinates
     * @return ContentInterface
     */
    public function setCoordinates(Coordinates $coordinates) :  ContentInterface
    {
        $this->coordinates = $coordinates;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return ContentInterface
     */
    public function unsetCoordinates() :  ContentInterface
    {
        unset($this->coordinates);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return float
     */
    public function getDistance() : float
    {
        return $this->distance;
    }

    /**
     * {@inheritdoc}
     *
     * @param float $distance
     * @return ContentInterface
     */
    public function setDistance(float $distance) :  ContentInterface
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getDuration() : int
    {
        return $this->duration;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $duration
     * @return ContentInterface
     */
    public function setDuration(int $duration) :  ContentInterface
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getDurationUnit() : string
    {
        return $this->durationUnit;
    }

    /**
     * @param string $durationUnit
     */
    public function setDurationUnit(string $durationUnit) :  ContentInterface
    {
        $this->durationUnit = $durationUnit;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean $frontPage
     */
    public function getFrontPage() : bool
    {
        return $this->frontPage;
    }

    /**
     * {@inheritdoc}
     *
     * @param boolean $frontPage
     * @return ContentInterface
     */
    public function setFrontPage(bool $frontPage) :  ContentInterface
    {
        $this->frontPage = $frontPage;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getLanguage() : string
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $language
     * @return ContentInterface
     */
    public function setLanguage(string $language) :  ContentInterface
    {
        $this->language = $language;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return ContentInterface
     */
    public function getPrincipal() : Content
    {
        return $this->principal;
    }

    /**
     * {@inheritdoc}
     *
     * @param \TheScienceTour\ContentPatternBundle\Model\Content $principal
     */
    public function setPrincipal(Content $principal) : Content
    {
        $this->principal = $principal;
        return $this;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $translations
     * @return ContentInterface
     */
    public function setTranslations($translations) :  ContentInterface
    {
        $this->translations = $translations;
        return $this;
    }
}