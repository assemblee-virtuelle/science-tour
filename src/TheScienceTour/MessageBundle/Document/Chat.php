<?php
namespace TheScienceTour\MessageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\Document(repositoryClass="TheScienceTour\MessageBundle\Repository\ChatRepository", requireIndexes=true)
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"users.id"="asc"}),
 *   @MongoDB\Index(keys={"updatedAt"="asc"})
 * })
 */

class Chat {

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $title;

	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $users;

	/**
	 * @Gedmo\Timestampable
	 * @MongoDB\Date
	 */
	protected $updatedAt;

	/**
	 * @MongoDB\EmbedMany(targetDocument="TheScienceTour\MessageBundle\Document\Message")
	 */
	protected $messages;

	/**
	 * @MongoDB\Field(type="bool")
	 */
	protected $private;

	public function __construct() {
		$this->users = new \Doctrine\Common\Collections\ArrayCollection();
		$this->messages = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getUsers() {
		return $this->users;
	}

	public function getUpdatedAt() {
		return $this->updatedAt;
	}

	public function getMessages() {
		return $this->messages;
	}

	public function getPrivate() {
		return $this->private;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function addUser($user) {
		if (!$this->users->contains($user)) {
			$this->users[] = $user;
		}
	}

	public function removeUser($user) {
		$this->users->removeElement($user);
	}

	public function addMessage($message) {
		$this->messages[] = $message;
	}

	public function removeMessage($message) {
		$this->messages->removeElement($message);
	}

	public function setPrivate($private) {
		$this->private = $private;
	}

}
