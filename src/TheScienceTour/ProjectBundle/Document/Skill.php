<?php
namespace TheScienceTour\ProjectBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @MongoDB\Document(repositoryClass="TheScienceTour\ProjectBundle\Repository\SkillRepository", requireIndexes=true)
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"helpers.id"="asc"}),
 * })
 */

class Skill {

	/**
	 * @MongoDB\Id
	 */
	protected $id;
	
	/**
	 * @MongoDB\Field(type="String")
	 * @Assert\NotBlank(groups={"publish"})
	 */
	protected $name;

	/**
	 * @MongoDB\Field(type="Int")
	 * @Assert\Type(type="integer", groups={"publish"})
	 * @Assert\NotBlank(groups={"publish"})
	 * @Assert\Range(min=0, groups={"publish"})
	 */
	protected $number;

	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $helpers;

	public function __construct() {
		$this->helpers = new \Doctrine\Common\Collections\ArrayCollection();
	}

    public function getId() {
        return $this->id;
    }

    public function getName() {
    	return $this->name;
    }

    public function getNumber() {
    	return $this->number;
    }

    public function getHelpers() {
    	return $this->helpers;
    }

    public function getPercent() {
    	return intval(100*$this->helpers->count()/$this->getNumber());
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setNumber($number) {
    	$this->number = $number;
    }

    public function addHelper($helper) {
    	$this->helpers[] = $helper;
    }

    public function removeHelper($helper) {
    	$this->helpers->removeElement($helper);
    }

}
