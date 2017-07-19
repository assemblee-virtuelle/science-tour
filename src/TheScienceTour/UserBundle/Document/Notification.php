<?php 
namespace TheScienceTour\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Notification {
	
	/**
	 * @MongoDB\String
	 */
	protected $docType;
	
	/**
	 * @MongoDB\Id
	 */
	protected $docId;

	public function __construct($docType, $docId) {
		$this->docType = $docType; // chat / project-resources / project-news / project-chats
		$this->docId = $docId;
	}
	
	public function getDocType() {
		return $this->docType;
	}
	
	public function getDocId() {
		return $this->docId;
	}
	
	public function setDocType($docType) {
		$this->docType = $docType;
	}
	
	public function setDocId($docId) {
		$this->docId = $docId;
	}
}
