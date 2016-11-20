<?php
namespace TheScienceTour\ChallengeBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;
use TheScienceTour\DocumentBundle\Document\Document as BaseDocument;

/**
 * @MongoDB\Document(repositoryClass="TheScienceTour\ChallengeBundle\Repository\ChallengeRepository", requireIndexes=true)
 * @MongoDBUnique(fields="title", message="There is already a challenge with that title.")
 * @MongoDB\Indexes({
 *   @MongoDB\UniqueIndex(keys={"title"="asc"}),
 *   @MongoDB\Index(keys={"createdAt"="asc"}),
 *   @MongoDB\Index(keys={"updatedAt"="asc"}),
 *   @MongoDB\Index(keys={"startedAt"="asc"}),
 *   @MongoDB\Index(keys={"finishedAt"="asc"}),
 *   @MongoDB\Index(keys={"publishedAt"="asc"}),
 *   @MongoDB\Index(keys={"isErasmus"="asc"})
 * })
 */

class Challenge extends BaseDocument {

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $creator;

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
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist", "remove"})
	 * @Assert\NotNull()
	 * @TSTMediaAssert\ValidImgRes()
	 * @TSTMediaAssert\ValidImgSize()
	 */
	protected $picture;

	/**
	 * @MongoDB\String
	 * @Assert\NotBlank()
	 */
	protected $description; // Goal of the challenge

	/**
	 * @MongoDB\String
	 * @Assert\NotBlank()
	 */
	protected $rules; // Rules of the challenge

	/**
	 * @MongoDB\Int
	 * @Assert\Type(type="integer")
	 * @Assert\NotBlank()
	 * @Assert\Range(min=0)
	 */
	protected $duration;

	/**
	 * @MongoDB\String
	 * @Assert\NotBlank()
	 * @Assert\Choice({"day", "week", "month"})
	 */
	protected $durationUnit;

	/**
	 * @MongoDB\EmbedMany(targetDocument="TheScienceTour\ChallengeBundle\Document\ChallengeRes")
	 */
	protected $tools;

	/**
	 * @MongoDB\EmbedMany(targetDocument="TheScienceTour\ChallengeBundle\Document\ChallengeRes")
	 */
	protected $materials;

	/**
	 * @MongoDB\EmbedMany(targetDocument="TheScienceTour\ChallengeBundle\Document\ChallengeRes")
	 */
	protected $premises;

	/**
	 * @MongoDB\EmbedMany(targetDocument="TheScienceTour\ChallengeBundle\Document\ChallengeRes")
	 */
	protected $skills;

	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\UserBundle\Document\User")
	 */
	protected $subscribers;

	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\MessageBundle\Document\Chat", cascade={"persist", "remove"})
	 */
	protected $chats;

	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\ProjectBundle\Document\Project", mappedBy="challenge")
	 */
	protected $projects;

	// /**
	//  * @MongoDB\Boolean
	//  */
	// protected $isErasmus; // Le contenu est-il lié à un projet Erasmus ?
	//
	// /**
	//  * @MongoDB\String
	//  */
	// protected $language; // La langue du document
	//
	// /**
	//  * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\ChallengeBundle\Document\Challenge", inversedBy="translations")
	//  */
	// protected $principal; // Le document original
	//
	// /**
	//  * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\ChallengeBundle\Document\Challenge", mappedBy="principal")
	//  */
	// protected $translations; // Ensemble des traductions

	public function __construct() {
		$this->tools = new \Doctrine\Common\Collections\ArrayCollection();
		$this->materials = new \Doctrine\Common\Collections\ArrayCollection();
		$this->premises = new \Doctrine\Common\Collections\ArrayCollection();
		$this->skills = new \Doctrine\Common\Collections\ArrayCollection();
		$this->qubscribers = new \Doctrine\Common\Collections\ArrayCollection();
		$this->projects = new \Doctrine\Common\Collections\ArrayCollection();
	}



    public function getId() {
        return $this->id;
    }

    public function getCreator() {
    	return $this->creator;
    }

    public function getCreatedAt() {
    	return $this->createdAt;
    }

    public function getUpdatedAt() {
    	return $this->updatedAt;
    }

    public function getStartedAt() {
    	return $this->startedAt;
    }

    public function getFinishedAt() {
    	return $this->finishedAt;
    }

    public function getTitle() {
    	return $this->title;
    }

    public function getTitleForChoiceList() {
    	$CLtitle = $this->title;
    	$today = new \DateTime();
    	if ($this->finishedAt < $today) {
    		$CLtitle .= " (fini)";
    	}
    	return $CLtitle;
    }

    public function getPicture() {
    	return $this->picture;
    }

    public function getDescription() {
    	return $this->description;
    }

    public function getRules() {
    	return $this->rules;
    }

    public function getDuration() {
    	return $this->duration;
    }

    public function getDurationUnit() {
    	return $this->durationUnit;
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
    	$contributors = new \Doctrine\Common\Collections\ArrayCollection();
    	foreach ($this->projects as $project) {
    		if ($project->getStatus() != 0) {
    			$creator = $project->getCreator();
    			if (!$contributors->contains($creator)) {
    				$contributors[] = $creator;
    			}
    		}
    	}
    	return $contributors;
    }

    public function getSubscribers() {
    	return $this->subscribers;
    }

    public function getChats() {
    	return $this->chats;
    }

    public function getProjects() {
    	$publishedProjects = new \Doctrine\Common\Collections\ArrayCollection();
    	foreach ($this->projects as $project) {
    		if ($project->getStatus() != 0) {
    			$publishedProjects[] = $project;
    		}
    	}
    	return $publishedProjects;
    }

    public function getRemainingTime() {
    	$today = new \DateTime();
    	return $today->diff($this->finishedAt);
    }

    public function getPercentTime() {
    	$today = new \DateTime();
    	if ($this->finishedAt < $today) {
    		return 100;
    	}
    	if ($this->startedAt > $today) {
    		return 0;
    	}
    	$interval = date_diff($today, $this->startedAt);
    	$duration = date_diff($this->startedAt, $this->finishedAt);
    	$percent = floor(100*($interval->y*8766+$interval->m*730+$interval->d*24+$interval->h)/($duration->y*8766+$duration->m*730+$duration->d*24+$duration->h));
    	return $percent;
    }



    public function setCreator($creator) {
    	$this->creator = $creator;
    }

    public function setCreatedAt($createdAt) {
    	$this->createdAt = $createdAt;
    	return $this;
    }

    public function setUpdatedAt($updatedAt) {
    	$this->updatedAt = $updatedAt;
    	return $this;
    }

    public function setStartedAt($startedAt) {
    	$this->startedAt = $startedAt;
    	return $this;
    }

    public function setFinishedAt($finishedAt) {
    	$this->finishedAt = $finishedAt;
    	return $this;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setPicture($picture) {
    	$this->picture = $picture;
    	if ($picture != null) {
    		$this->picture->setName($this->title);
    	}
    }

    public function setDescription($description) {
    	$this->description = $description;
    }

    public function setRules($rules) {
    	$this->rules = $rules;
    }

    public function setDuration($duration) {
    	$this->duration = $duration;
    }

    public function setDurationUnit($durationUnit) {
    	$this->durationUnit = $durationUnit;
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

    public function addSubscriber($subscriber) {
    	if (!$this->subscribers->contains($subscriber)) {
    		$this->subscribers[] = $subscriber;
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
    	$this->premises->remove($premise);
    }

    public function removeSkill($skill) {
    	$this->skills->removeElement($skill);
    }

    public function removeSubscriber($subscriber) {
    	$this->subscribers->removeElement($subscriber);
    }

    public function removeChat($chat) {
    	$this->chats->removeElement($chat);
    }


    public function removeRes() {
    	$this->tools = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->materials = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->premises = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->skills = new \Doctrine\Common\Collections\ArrayCollection();
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

	// public function getIsErasmus() {
	// 	return $this->isErasmus;
	// }
	//
	// public function getLanguage() {
	// 	return $this->isErasmus;
	// }
	//
	// public function getPrincipal() {
	// 	return $this->isErasmus;
	// }
	//
	// public function setIsErasmus($isErasmus) {
	// 	$this->isErasmus = $isErasmus;
	// 	return $this;
	// }
	//
	// public function setLanguage($language) {
	// 	$this->language = $language;
	// 	return $this;
	// }
	//
	// public function setPrincipal(\TheScienceTour\ChallengeBundle\Document\Challenge $principal) {
	// 	$this->principal = $principal;
	// 	return $this;
	// }
	//
	// public function getTranslations() {
	// 	return $this->translations;
    // }
	//
	// public function addTranslation(\TheScienceTour\ChallengeBundle\Document\Challenge $translation) {
	// 	$this->translations[] = $translation;
	// }
	//
	// public function removeTranslation(\TheScienceTour\ChallengeBundle\Document\Challenge $translation) {
	// 	$this->translations->removeElement($translation);
	// }
}
