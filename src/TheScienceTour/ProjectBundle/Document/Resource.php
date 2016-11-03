<?php 
namespace TheScienceTour\ProjectBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @MongoDB\Document
 */

class Resource {
	
	/**
	 * @MongoDB\Id
	 */
	protected $id;
	
	/**
	 * @MongoDB\String
	 * @Assert\NotBlank(groups={"publish"})
	 */
	protected $name;

	/**
	 * @MongoDB\Int
	 * @Assert\Type(type="integer", groups={"publish"})
	 * @Assert\NotBlank(groups={"publish"})
	 * @Assert\Range(min=1, max=99, groups={"publish"})
	 */
	protected $number;
	
	/**
	 * @MongoDB\Int
	 * @Assert\Type(type="integer", groups={"publish"})
	 * @Assert\NotBlank(groups={"publish"})
	 * @Assert\Range(min=0, max=99, groups={"publish"})
	 */
	protected $creatorHelpNb;
	
	/**
	 * @MongoDB\EmbedMany(targetDocument="TheScienceTour\ProjectBundle\Document\Help")
	 */
	protected $helps;

	public function __construct() {
		$this->helps = new \Doctrine\Common\Collections\ArrayCollection();
		$this->creatorHelpNb = 0;
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
    
    public function getCreatorHelpNb() {
    	return $this->creatorHelpNb;
    }
    
    public function getHelps() {
    	return $this->helps;
    }
    
    public function getUncompletedHelps() {
    	$uncompletedHelps = new \Doctrine\Common\Collections\ArrayCollection();
    	foreach ($this->helps as $help) {
    		if ($help->getNbReceived() == 0) {
    			$uncompletedHelps[] = $help;
    		}
    	}
    	return $uncompletedHelps;
    }
    
    public function getActualNumber() {
    	$actualNb = $this->getCreatorHelpNb();
    	foreach ($this->helps as $help) {
    		$actualNb += $help->getNbReceived();
    	}
    	return $actualNb;
    }
    
    public function getPercent() {
    	if ($this->getNumber()<1) {
    		return 0;
    	}
    	$percent = intval(100*$this->getActualNumber()/$this->getNumber());
    	if ($percent >= 100) {$percent = 100;}
    	return $percent;
    }
    
    public function getUntreatedProposedHelpNumber() {
    	$number = 0;
    	foreach ($this->helps as $help) {
    		if ($help->getNbNeeded() == 0 && $help->getNbReceived() == 0) {
    			$number += $help->getNbProposed();
    		}
    	}
    	return $number;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setNumber($number) {
    	$this->number = $number;
    }
    
    public function setCreatorHelpNb($creatorHelpNb) {
    	$this->creatorHelpNb = $creatorHelpNb;
    }
    
    public function addHelp($help) {
    	$this->helps[] = $help;
    }
    
    public function removeHelp($help) {
    	$this->helps->removeElement($help);
    }
    
    public function containsHelper($helper) {
    	foreach ($this->helps as $help) {
    		if ($help->getHelper() == $helper) {
    			return True;
    		}
		}
		return False;
    }
    
    public function isHelpCompleted($helper) {
    	if (!$this->containsHelper($helper)) {
    		return False;
    	}
    	foreach ($this->helps as $help) {
    		if ($help->getHelper() == $helper) {
    			return ($help->getNbReceived() != 0);
    		}
    	}
    }
       
    public function getHelp($helper) {
    	foreach ($this->helps as $help) {
    		if ($help->getHelper() == $helper) {
    			return $help;
    		}
    	}
    	return null;
    }
    
}
