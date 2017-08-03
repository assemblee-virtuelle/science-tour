<?php
namespace TheScienceTour\MessageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\EmbeddedDocument
 */

class Message {

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $author;

	/**
	 * @Gedmo\Timestampable(on="create")
	 * @MongoDB\Date
	 */
	protected $createdAt;

	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $unreadBy;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $content;

	public function __construct() {
		$this->unreadBy = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getAuthor() {
		return $this->author;
	}

	public function getCreatedAt() {
		return $this->createdAt;
	}

	public function getUnreadBy() {
		return $this->unreadBy;
	}

	public function getContent() {
		return $this->content;
	}

	public function setAuthor($author) {
		$this->author = $author;
	}

	public function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function addUnreadBy($user) {
		$this->unreadBy[] = $user;
	}

	public function removeUnreadBy($user) {
		$this->unreadBy->removeElement($user);
	}

}
