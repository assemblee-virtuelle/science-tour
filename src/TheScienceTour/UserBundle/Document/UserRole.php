<?php 
namespace TheScienceTour\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;

/**
 * @MongoDB\EmbeddedDocument
 */
class UserRole {
	
	/**
	 * @MongoDB\String
	 * @Assert\NotBlank()
	 */
	protected $organization;
	
	/**
	 * @MongoDB\String
	 */
	protected $job;
	
	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist"})
	 * @TSTMediaAssert\ValidImgRes()
	 * @TSTMediaAssert\ValidImgSize()
	 */
	protected $picture;
	
	public function getOrganization() {
		return $this->organization;
	}
	
	public function getJob() {
		return $this->job;
	}
	
	public function getPicture() {
		return $this->picture;
	}
	
	public function setOrganization($organization) {
		$this->organization = $organization;
	}
	
	public function setJob($job) {
		$this->job = $job;
	}
	
	public function setPicture($picture) {
		$this->picture = $picture;
	}
	

}