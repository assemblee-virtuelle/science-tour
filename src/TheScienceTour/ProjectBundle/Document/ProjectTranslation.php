<?php
namespace TheScienceTour\ProjectBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use TheScienceTour\MapBundle\Validator\Constraints as TSTMapAssert;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;
use TheScienceTour\ProjectBundle\Validator\Constraints as TSTProjectAssert;
use TheScienceTour\DocumentBundle\Document\Document as BaseDocument;

/**
 * @MongoDB\Document(repositoryClass="TheScienceTour\ProjectBundle\Repository\ProjectRepository", requireIndexes=true)
 * @MongoDBUnique(fields="title", message="There is already a project with that title.")
 * @MongoDB\Indexes({
 *   @MongoDB\UniqueIndex(keys={"title"="asc"}),
 *   @MongoDB\Index(keys={"translator.id"="asc"}),
 *   @MongoDB\Index(keys={"status"="asc"}),
 *   @MongoDB\Index(keys={"createdAt"="asc"}),
 *   @MongoDB\Index(keys={"updatedAt"="asc"}),
 *   @MongoDB\Index(keys={"startedAt"="asc"}),
 *   @MongoDB\Index(keys={"finishedAt"="asc"}),
 *   @MongoDB\Index(keys={"publishedAt"="asc"}),
 *   @MongoDB\Index(keys={"language"="asc"})
 *   @MongoDB\Index(keys={"original.id"="asc"})
 * })
 * @TSTProjectAssert\AtLeastOneRes(groups={"publish"})
 */
class ProjectTranslation /* extends BaseDocument */ {

  /**
   * @MongoDB\Id
   */
  protected $id;

  /**
   * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\UserBundle\Document\User")
   */
  protected $translator;

  /**
   * @MongoDB\Int
   */
  protected $status; // 0: draft, 1: published

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
   * @MongoDB\ReferenceOne(targetDocument="Project", mappedBy="translations")
   */
  protected $original;

  /**
   * @MongoDB\String
   * @Assert\NotBlank()
   * @Assert\Length(min="0", max="100")
   */
  protected $title;

  /**
   * @MongoDB\String
   * @Assert\NotBlank(groups={"publish"})
   */
  protected $goal; // Goal of the game

  /**
   * @MongoDB\String
   * @Assert\NotBlank(groups={"publish"})
   */
  protected $description; // Rules of the game

  /**
   * @MongoDB\String
   */
  protected language;

  public function __construct() {
    $this->frontPage = FALSE;
    $this->tools     = new \Doctrine\Common\Collections\ArrayCollection();
    $this->materials = new \Doctrine\Common\Collections\ArrayCollection();
    $this->premises  = new \Doctrine\Common\Collections\ArrayCollection();
    $this->skills    = new \Doctrine\Common\Collections\ArrayCollection();
  }


  public function getId() {
    return $this->id;
  }

  public function getTranslator() {
    return $this->translator;
  }

  public function getOriginal() {
    return $this->original;
  }

  public function getTitle() {
    return $this->title;
  }

  public function getStatus() {
    return $this->status;
  }

  public function getGoal() {
    return $this->goal;
  }

  public function getDescription() {
    return $this->description;
  }

  public function getLanguage() {
    return $this->language;
  }

  public function setOriginal($project) {
    $this->original = $project;
  }

  public function setTitle($title) {
    $this->title = $title;
  }

  public function setTranslator($translator) {
    $this->creator = translator;
  }

  public function setStatus($status) {
    $this->status = $status;
  }

  public function setGoal($goal) {
    $this->goal = $goal;
  }

  public function setDescription($description) {
    $this->description = $description;
  }

  public function setLanguage($language) {
    $this->language = $language;
  }

  /**
   * Set createdAt
   *
   * @param date $createdAt
   *
   * @return self
   */
  public function setCreatedAt($createdAt) {
    $this->createdAt = $createdAt;
    return $this;
  }

  /**
   * Get createdAt
   *
   * @return date $createdAt
   */
  public function getCreatedAt() {
    return $this->createdAt;
  }

  /**
   * Set updatedAt
   *
   * @param date $updatedAt
   *
   * @return self
   */
  public function setUpdatedAt($updatedAt) {
    $this->updatedAt = $updatedAt;
    return $this;
  }

  /**
   * Get updatedAt
   *
   * @return date $updatedAt
   */
  public function getUpdatedAt() {
    return $this->updatedAt;
  }

  /**
   * Set publishedAt
   *
   * @param date $publishedAt
   *
   * @return self
   */
  public function setPublishedAt($publishedAt) {
    $this->publishedAt = $publishedAt;
    return $this;
  }

  /**
   * Get publishedAt
   *
   * @return date $publishedAt
   */
  public function getPublishedAt() {
    return $this->publishedAt;
  }
}
