<?php 
namespace TheScienceTour\ProjectBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\EmbeddedDocument
 */

class Help {
	
	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $helper;
	
	/**
	 * @MongoDB\Int
	 * @Assert\Type(type="integer")
	 * @Assert\NotBlank()
	 * @Assert\Range(min=1)
	 */
	protected $nbProposed;
	
	/**
	 * @MongoDB\Int
	 * @Assert\Type(type="integer")
	 * @Assert\NotBlank()
	 * @Assert\Range(min=1)
	 */
	protected $nbNeeded;
	
	/**
	 * @MongoDB\Int
	 * @Assert\Type(type="integer")
	 * @Assert\Range(min=0)
	 */	
	protected $nbReceived;
	
	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MessageBundle\Document\Chat", cascade={"persist", "remove"})
	 */
	protected $chat;

	public function __construct() {
		$this->nbReceived = 0;
		$this->nbNeeded = 0;
	}
	
	public function getHelper() {
		return $this->helper;
	}
	
	public function getNbProposed() {
		return $this->nbProposed;
	}
	
	public function getNbNeeded() {
		if ($this->nbNeeded) {
			return $this->nbNeeded;
		} else {
			return 0;
		}
	}
	
	public function getNbReceived() {
		return $this->nbReceived;
	}
	
	public function getChat() {
		return $this->chat;
	}

	public function setHelper($helper) {
		$this->helper = $helper;
	}
	
	public function setNbProposed($nbProposed) {
		$this->nbProposed = $nbProposed;
	}
	
	public function setNbNeeded($nbNeeded) {
		$this->nbNeeded = $nbNeeded;
	}
	
	public function setNbReceived($nbReceived) {
		$this->nbReceived = $nbReceived;
	}
	
	public function setChat($chat) {
		$this->chat = $chat;
	}
}
