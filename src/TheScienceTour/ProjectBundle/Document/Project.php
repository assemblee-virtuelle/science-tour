<?php 
namespace TheScienceTour\ProjectBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use TheScienceTour\MapBundle\Validator\Constraints as TSTMapAssert;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;
use TheScienceTour\ProjectBundle\Validator\Constraints as TSTProjectAssert;

/**
 * @MongoDB\Document(repositoryClass="TheScienceTour\ProjectBundle\Repository\ProjectRepository", requireIndexes=true)
 * @MongoDBUnique(fields="title", message="There is already a project with that title.")
 * @MongoDB\Indexes({
 *   @MongoDB\UniqueIndex(keys={"title"="asc"}),
 *   @MongoDB\Index(keys={"creator.id"="asc"}),
 *   @MongoDB\Index(keys={"status"="asc"}),
 *   @MongoDB\Index(keys={"contributors.id"="asc"}),
 *   @MongoDB\Index(keys={"supporters.id"="asc"}),
 *   @MongoDB\Index(keys={"subscribers.id"="asc"}),
 *   @MongoDB\Index(keys={"sponsors.id"="asc"}),
 *   @MongoDB\Index(keys={"skills.id"="asc"}),
 *   @MongoDB\Index(keys={"createdAt"="asc"}),
 *   @MongoDB\Index(keys={"updatedAt"="asc"}),
 *   @MongoDB\Index(keys={"startedAt"="asc"}),
 *   @MongoDB\Index(keys={"finishedAt"="asc"}),
 *   @MongoDB\Index(keys={"publishedAt"="asc"}),
 *   @MongoDB\Index(keys={"place"="asc"}),
 *   @MongoDB\Index(keys={"price"="asc"}),
 *   @MongoDB\Index(keys={"frontPage"="desc"}),
 *   @MongoDB\Index(keys={"coordinates"="2d"}),
 *   @MongoDB\Index(keys={"challenge.id"="asc"})
 * })
 * @TSTProjectAssert\AtLeastOneRes(groups={"publish"})
 */

class Project {
	
	/**
	 * @MongoDB\Id
	 */
	protected $id;
	
	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $creator;
	
	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $delegate;
	
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
	 * @MongoDB\Date
	 */
	protected $startedAt;

	/**
	 * @MongoDB\Date
	 */
	protected $finishedAt;
	
	/**
	 * @MongoDB\String
	 * @Assert\NotBlank()
	 * @Assert\Length(min="0", max="100")
	 */
	protected $title;

	/**
	 * @MongoDB\String
	 * @Assert\NotBlank(groups={"publish"})
	 * @TSTMapAssert\ProvidedAddress(groups={"publish"})
	 */
	protected $place;
	
	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist", "remove"})
	 * @Assert\NotNull()
	 * @TSTMediaAssert\ValidImgRes()
	 * @TSTMediaAssert\ValidImgSize()
	 */
	protected $picture;
	
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
	 * @MongoDB\Int
	 * @Assert\Type(type="integer", groups={"publish"})
	 * @Assert\NotBlank(groups={"publish"})
	 * @Assert\Range(min=0, groups={"publish"})
	 */
	protected $duration;

	/**
	 * @MongoDB\String
	 * @Assert\NotBlank(groups={"publish"})
     * @Assert\Choice({"day", "week", "month"})
	 */
	protected $durationUnit;
	
	/**
	 * @MongoDB\Int
	 * @Assert\Type(type="integer", groups={"publish"})
	 * @Assert\Range(min=0, groups={"publish"})
	 */
	protected $price;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\ProjectBundle\Document\Resource", cascade={"persist", "remove"})
     */
	protected $tools;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\ProjectBundle\Document\Resource", cascade={"persist", "remove"})
	 */
	protected $materials;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\ProjectBundle\Document\Resource", cascade={"persist", "remove"})
	 */
	protected $premises;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\ProjectBundle\Document\Skill", cascade={"persist", "remove"})
	 */
	protected $skills;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $contributors;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $supporters;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $subscribers;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $sponsors;
	
	/**
	 * @MongoDB\EmbedOne(targetDocument="TheScienceTour\MapBundle\Document\Coordinates")
	 */
	protected $coordinates;
	
	/**
	 * @MongoDB\Distance
	 */
	protected $distance;
	
	/**
	 * @MongoDB\Boolean
	 */
	protected $frontPage;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\MessageBundle\Document\Chat", cascade={"persist", "remove"})
	 */
	protected $chats;
	
	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\ChallengeBundle\Document\Challenge", inversedBy="projects")
	 */
	protected $challenge;
	
		
	public function __construct() {
		$this->frontPage = false;
		$this->tools = new \Doctrine\Common\Collections\ArrayCollection();
		$this->materials = new \Doctrine\Common\Collections\ArrayCollection();
		$this->premises = new \Doctrine\Common\Collections\ArrayCollection();
		$this->skills = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	
    public function getId() {
        return $this->id;
    }
    
    public function getCreator() {
    	return $this->creator;
    }
    
    public function getDelegate() {
    	return $this->delegate;
    }
    
    public function getTitle() {
    	return $this->title;
    }
    
    public function getStatus() {
    	return $this->status;
    }
    
    public function getPlace() {
    	return $this->place;
    }
    
    public function getPicture() {
    	return $this->picture;
    }
    
    public function getGoal() {
    	return $this->goal;
    }  
    
    public function getDescription() {
    	return $this->description;
    }
    
    public function getDuration() {
    	return $this->duration;
    }
    
    public function getDurationUnit() {
    	return $this->durationUnit;
    }
    
    public function getPrice() {
    	return $this->price;
    }

    public function getTools() {
    	return $this->tools;
    }
    
    public function getMaterials() {
    	return $this->materials;
    }
    
    public function getPremises() {
    	return $this->premises;
    }
    
    public function getSkills() {
    	return $this->skills;
    }
    
    public function getContributors() {
    	return $this->contributors;
    }
    
    public function getSupporters() {
    	return $this->supporters;
    }
    
    public function getSubscribers() {
    	return $this->subscribers;
    }
    
    public function getSponsors() {
    	return $this->sponsors;
    }
    
    public function getChats() {
    	return $this->chats;
    }
    
    public function getChallenge() {
    	return $this->challenge;
    }
    
    public function getSkillFraction() {
    	$actualNumber = 0;
    	$totalNumber = 0;
    	foreach ($this->skills as $skill) {
    		$actualNumber += $skill->getHelpers()->count();
    		$totalNumber += $skill->getNumber();
    	}
    	return $actualNumber.' / '.$totalNumber;
    }
    
    public function getTeam() {
    	$team = new \Doctrine\Common\Collections\ArrayCollection();
    	$team[] = $this->creator;
    	foreach ($this->contributors as $contributor) {
    		if (!$team->contains($contributor)) {
    			$team[] = $contributor;
    		}
    	}
    	foreach ($this->skills as $skill) {
    		foreach ($skill->getHelpers() as $helper) {
    			if (!$team->contains($helper)) {
    				$team[] = $helper;
    			}
    		}
    	}
    	foreach ($this->sponsors as $sponsor) {
    		if (!$team->contains($sponsor)) {
    			$team[] = $sponsor;
    		}
    	}
    	return $team;
    }
    
    public function getEverybody() {
    	$everybody = $this->getTeam();
    	foreach ($this->supporters as $supporter) {
    		if (!$everybody->contains($supporter)) {
    			$everybody[] = $supporter;
    		}
    	}
    	foreach ($this->subscribers as $subscriber) {
    		if (!$everybody->contains($subscriber)) {
    			$everybody[] = $subscriber;
    		}
    	}
    	return $everybody;
    }
    
    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function setCreator($creator) {
    	$this->creator = $creator;
    }
    
    public function setDelegate($delegate) {
    	$this->delegate = $delegate;
    }
    
    public function setStatus($status) {
    	$this->status = $status;
    }
    
    public function setPlace($place) {
    	$this->place = $place;
    }

    public function setPicture($picture) {
    	$this->picture = $picture;
    	if ($picture != null) {
    		$this->picture->setName($this->title);
    	}
    }
    
    public function setGoal($goal) {
        $this->goal = $goal;
    }

    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function setDuration($duration) {
    	$this->duration = $duration;
    }
    
    public function setDurationUnit($durationUnit) {
    	$this->durationUnit = $durationUnit;
    }
    
    public function setPrice($price) {
    	$this->price = $price;
    }
    
    public function setChallenge($challenge) {
    	$this->challenge = $challenge;
    }
    
    public function setSupporters() {
    	$supporters = new \Doctrine\Common\Collections\ArrayCollection();
    	foreach ($this->tools as $tool) {
    		foreach ($tool->getHelps() as $help) {
    			if ($help->getNbReceived() > 0 && !$supporters->contains($help->getHelper())) {
    				$supporters[] = $help->getHelper();
    			}
    		}
    	}
    	foreach ($this->materials as $material) {
    		foreach ($material->getHelps() as $help) {
    			if ($help->getNbReceived() > 0 && !$supporters->contains($help->getHelper())) {
    				$supporters[] = $help->getHelper();
    			}
    		}
    	}
    	foreach ($this->premises as $premise) {
    		foreach ($premise->getHelps() as $help) {
    			if ($help->getNbReceived() > 0 && !$supporters->contains($help->getHelper())) {
    				$supporters[] = $help->getHelper();
    			}
    		}
    	}
    	$this->supporters = $supporters;
    }
    
    public function addTool($tool) {
    	$this->tools[] = $tool;
    }
    
    public function addMaterial($material) {
    	$this->materials[] = $material;
    }
    
    public function addPremise($premise) {
    	$this->premises[] = $premise;
    }
    
    public function addSkill($skill) {
    	$this->skills[] = $skill;
    }
    
    public function addContributor($contributor) {
    	if (!$this->contributors->contains($contributor)) {
    		$this->contributors[] = $contributor;
    	}
    }
    
    public function addSubscriber($subscriber) {
    	if (!$this->subscribers->contains($subscriber)) {
    		$this->subscribers[] = $subscriber;
    	}
    }
    
    public function addSponsor($sponsor) {
    	if (!$this->sponsors->contains($sponsor)) {
    		$this->sponsors[] = $sponsor;
    	}
    }
    
    public function addChat($chat) {
    	$this->chats[] = $chat;
    }
    
    public function removeTool($tool) {
    	$this->tools->removeElement($tool);
    }
    
    public function removeMaterial($material) {
    	$this->materials->removeElement($material);
    }
    
    public function removePremise($premise) {
    	$this->premises->removeElement($premise);
    }
    
    public function removeSkill($skill) {
    	$this->skills->removeElement($skill);
    }
    
    public function removeContributor($contributor) {
    	$this->contributors->removeElement($contributor);
    }
    
    public function removeSubscriber($subscriber) {
    	$this->subscribers->removeElement($subscriber);
    }
    
    public function removeSponsor($sponsor) {
    	$this->sponsors->removeElement($sponsor);
    }
    
    public function eraseSponsors() {
    	$this->sponsors = null;
    }
    
    public function removeChat($chat) {
    	$this->chats->removeElement($chat);
    }

    /**
     * Set coordinates
     *
     * @param TheScienceTour\MapBundle\Document\Coordinates $coordinates
     * @return self
     */
    public function setCoordinates(\TheScienceTour\MapBundle\Document\Coordinates $coordinates)
    {
    	$this->coordinates = $coordinates;
    	return $this;
    }
    
    /**
     * Get coordinates
     *
     * @return TheScienceTour\MapBundle\Document\Coordinates $coordinates
     */
    public function getCoordinates()
    {
    	return $this->coordinates;
    }
    
    public function unsetCoordinates()
    {
    	unset($this->coordinates);
    }
    
    /**
     * Set distance
     *
     * @param string $distance
     * @return self
     */
    public function setDistance($distance)
    {
    	$this->distance = $distance;
    	return $this;
    }
    
    /**
     * Get distance
     *
     * @return string $distance
     */
    public function getDistance()
    {
    	return $this->distance;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param date $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return date $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set publishedAt
     *
     * @param date $publishedAt
     * @return self
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return date $publishedAt
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set startedAt
     *
     * @param date $startedAt
     * @return self
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    /**
     * Get startedAt
     *
     * @return date $startedAt
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set finishedAt
     *
     * @param date $finishedAt
     * @return self
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }
    
    public function updateFinishedAt() {
    	if ($this->startedAt != null) {
	    	$date = clone $this->startedAt;
	    	if ($this->durationUnit == "day") {
	    		$date->add(new \DateInterval('P'.$this->duration.'D'));
	    	} else if ($this->durationUnit == "week") {
	    		$date->add(new \DateInterval('P'.$this->duration.'W'));
	    	} else {
	    		$date->add(new \DateInterval('P'.$this->duration.'M'));
	    	}
	    	$this->finishedAt = $date;
    	} else {
    		$this->finishedAt = null;
    	}
    }

    /**
     * Get finishedAt
     *
     * @return date $finishedAt
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * Set frontPage
     *
     * @param boolean $frontPage
     * @return self
     */
    public function setFrontPage($frontPage)
    {
        $this->frontPage = $frontPage;
        return $this;
    }

    /**
     * Get frontPage
     *
     * @return boolean $frontPage
     */
    public function getFrontPage()
    {
        return $this->frontPage;
    }
    public function getTotalResNb() {
    	$nb = 0;
    	foreach ($this->tools as $tool) {
    		$nb += $tool->getNumber();
    	}
    	foreach ($this->materials as $material) {
    		$nb += $material->getNumber();
    	}
    	foreach ($this->premises as $premise) {
    		$nb += $premise->getNumber();
    	}
    	foreach ($this->skills as $skill) {
    		$nb += $skill->getNumber();
    	}
    	return $nb;
    }
}
