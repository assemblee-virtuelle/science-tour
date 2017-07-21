<?php
namespace TheScienceTour\ProjectBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;

/**
 * @MongoDB\Document(repositoryClass="TheScienceTour\ProjectBundle\Repository\NewsRepository")
 */

class News {

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $projectId;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $author;

	/**
	 * @MongoDB\Field(type="string")
	 * @Assert\NotBlank()
	 * @Assert\Regex(
	 *     pattern="/^\{%/",
	 *     match=false
	 * )
	 */
	protected $title;

	/**
	 * @MongoDB\Field(type="string")
	 * @Assert\NotBlank()
	 */
	protected $content;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist", "remove"})
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


	public function getId() {
		return $this->id;
	}

	public function getProjectId() {
		return $this->projectId;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getContent() {
		return $this->content;
	}

	public function getPicture() {
		return $this->picture;
	}

	public function getCreatedAt() {
		return $this->createdAt;
	}

	public function getUpdatedAt() {
		return $this->updatedAt;
	}


	public function setProjectId($projectId) {
		$this->projectId = $projectId;
	}

	public function setAuthor($author) {
		$this->author = $author;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function setPicture($picture) {
		$this->picture = $picture;
		$this->picture->setName($this->title);
	}

	public function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;
	}

	public function setUpdatedAt($updatedAt) {
		$this->updatedAt = $updatedAt;
	}

}
