<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 14/09/2017
 * Time: 17:13
 */

namespace TheScienceTour\ContentPatternBundle\Model;

use TheScienceTour\MapBundle\Document\Coordinates;
use TheScienceTour\UserBundle\Document\User;
use TheScienceTour\MediaBundle\Document\Media;

/**
 * Interface ContentInterface
 * @package TheScienceTour\ContentPatternBundle\Model
 *
 *  @author Michel Cadennes <michel.cadennes@sens-commun.fr>
 */
interface ContentInterface {

    /**
     * Retourne l'identifiant du contenu
     *
     * @return int
     */
    public function getId() : int;

    /**
     * Modifie l'identifiant du contenu

     * @param int $id
     * @return ContentInterface
     */
    public function setId(int $id) : self;

    /**
     * Retourne l'objet représentant le créateur du contenu
     *
     * @return \TheScienceTour\UserBundle\Document\User
     */
    public function getCreator() : User;

    /**
     * Modifie le créateur du contenu
     *
     * @param \TheScienceTour\UserBundle\Document\User $creator
     * @return mixed
     */
    public function setCreator(User $creator) : self;

    /**
     * Retourne le titre du contenu
     *
     * @return string
     */
    public function getTitle() : string;

    /**
     * Modifie le titre du contenu
     *
     * @param string $title
     * @return ContentInterface
     */
    public function setTitle(string $title) : self;

    /**
     * Retourne le texte du contenu
     *
     * @return string
     */
    public function getDescription() : string;

    /**
     * Modifie le texte du contenu

     * @param string $description
     * @return ContentInterface
     */
    public function setDescription(string $description) : self;

    /**
     * Retourne l'image d'illustration du contenu
     *
     * @return Media
     */
    public function getPicture() : Media;

    /**
     * Modifie l'image d'illustration du contenu

     * @param \TheScienceTour\MediaBundle\Document\Media $picture
     * @return ContentInterface
     */
    public function setPicture(Media $picture) : self;

    /**
     * Retourne la date de création du contenu
     *
     * @return \DateTime
     */
    public function getCreatedAt() : \DateTime;

    /**
     * Modifie la date de création du contenu
     * Cette date ne peut pas être changée une seconde fois
     *
     * @param \DateTime $createdAt
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt) : self;

    /**
     * Retourne la date de la dernière modification du contenu
     *
     * @return \DateTime
     */
    public function getUpdatedAt() : \DateTime;

    /**
     * Modifie la date de la plus récente modification du contenu
     *
     * @param \DateTime $updatedAt
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt) : self;

    /**
     * Retourne la date de publication du contenu
     *
     * @return \DateTime
     */
    public function getPublishedAt() : \DateTime;

    /**
     * {@inheritdoc}
     *
     * @param \DateTime $publishedAt
     * @return self
     */
    public function setPublishedAt(\DateTime $publishedAt) : self;

    /**
     * {@inheritdoc}
     *
     * @return \DateTime $startDate
     */
    public function getStartedAt() : \DateTime;

    /**
     * {@inheritdoc}
     *
     * @param \DateTime $startDate
     * @return self
     */
    public function setStartedAt(\DateTime $startDate) : self;

    /**
     * Get endDate
     *
     * @return \DateTime $endDate
     */
    public function getFinishedAt() : \DateTime;

    /**
     * {@inheritdoc}
     *
     * @param \DateTime $endDate
     * @return self
     */
    public function setFinishedAt(\DateTime $endDate) : self;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPlace() : string;

    /**
     * {@inheritdoc}

     * @param string $place
     * @return ContentInterface
     */
    public function setPlace($place) : self;

    /**
     * {@inheritdoc}
     *
     * @return \TheScienceTour\MapBundle\Document\Coordinates $coordinates
     */
    public function getCoordinates() : Coordinates;

    /**
     * {@inheritdoc}
     *
     * @param \TheScienceTour\MapBundle\Document\Coordinates $coordinates
     * @return self
     */
    public function setCoordinates(Coordinates $coordinates) : self;

    /**
     * {@inheritdoc}
     *
     * @return ContentInterface
     */
    public function unsetCoordinates() : self;

    /**
     * {@inheritdoc}
     *
     * @return float
     */
    public function getDistance() : float;

    /**
     * {@inheritdoc}
     *
     * @param float $distance
     * @return ContentInterface
     */
    public function setDistance(float $distance) : self;
    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getDuration() : int;

    /**
     * {@inheritdoc}
     *
     * @param mixed $duration
     * @return ContentInterface
     */
    public function setDuration(int $duration) : self;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getDurationUnit() : string;

    /**
     * {@inheritdoc}
     *
     * @param string $durationUnit
     * @return ContentInterface
     */
    public function setDurationUnit(string $durationUnit) : self;

    /**
     * {@inheritdoc}
     *
     * @return boolean $frontPage
     */
    public function getFrontPage() : bool;

    /**
     * {@inheritdoc}
     *
     * @param boolean $frontPage
     * @return self
     */
    public function setFrontPage(bool $frontPage) : self;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getLanguage() : string;
    /**
     * {@inheritdoc}
     *
     * @param string $language
     * @return ContentInterface
     */
    public function setLanguage(string $language) : self;

    /**
     * {@inheritdoc}
     *
     * @return ContentInterface
     */
    public function getPrincipal() : Content;

    /**
     * {@inheritdoc}
     *
     * @param \TheScienceTour\ContentPatternBundle\Model\Content $principal
     */
    public function setPrincipal(Content $principal) : Content;

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function getTranslations();

    /**
     * {@inheritdoc}
     *
     * @param mixed $translations
     * @return ContentInterface
     */
    public function setTranslations($translations) : self;

}