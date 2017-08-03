<?php
namespace TheScienceTour\ChallengeBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\EmbeddedDocument
 */

class ChallengeRes {

	/**
	 * @MongoDB\Field(type="string")
	 * @Assert\NotBlank()
	 */
	protected $name;

	/**
	 * @MongoDB\Field(type="int")
	 * @Assert\Type(type="integer")
	 * @Assert\NotBlank()
	 * @Assert\Range(min=0)
	 */
	protected $number;

	public function getName() {
		return $this->name;
	}

	public function getNumber() {
		return $this->number;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setNumber($number) {
		$this->number = $number;
	}

}
