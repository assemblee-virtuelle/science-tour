<?php

namespace TheScienceTour\UserBundle\Document;

use FOS\UserBundle\Document\User as BaseUser;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use TheScienceTour\MediaBundle\Validator\Constraints as TSTMediaAssert;


/**
 * @MongoDB\Document
 */
class User extends BaseUser {
	
	/**
	 * @MongoDB\Id
	 */
	protected $id;
	
	/**
	 * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\MediaBundle\Document\Media", cascade={"persist", "remove"})
	 * @TSTMediaAssert\ValidImgRes()
	 * @TSTMediaAssert\ValidImgSize()
	 */
	protected $avatar;
	
	/**
	 * @MongoDB\String
	 */
	protected $city;
	
	/**
	 * @MongoDB\String
	 */
	protected $description;
	
	/**
	 * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\EventBundle\Document\Event")
	 */
	protected $favoriteEvents;
	
	/**
	 * @MongoDB\EmbedMany(targetDocument="TheScienceTour\UserBundle\Document\Notification")
	 */
	protected $notifications;
	
	/**
	 * @MongoDB\String
	 */
	protected $info1; // for researcher: education
	
	/**
	 * @MongoDB\String
	 */
	protected $info2; // for researcher: research topics

	/**
	 * @MongoDB\String
	 */
	protected $info3; // for researcher: availability
	
	/**
	 * @MongoDB\EmbedMany(targetDocument="TheScienceTour\UserBundle\Document\UserRole")
	 */
	protected $userRoles;
	
	
	public function getId() {
		return $this->id;
	}
	
	public function getAvatar() {
		return $this->avatar;
	}
	
	public function getCity() {
		return $this->city;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getNotifications() {
		return $this->notifications;
	}
	
	public function getInfo1() {
		return $this->info1;
	}
	
	public function getInfo2() {
		return $this->info2;
	}
	
	public function getInfo3() {
		return $this->info3;
	}
	
	public function getUserRoles() {
		return $this->userRoles;
	}
	
	public function setAvatar($avatar) {
		$this->avatar = $avatar;
		$this->avatar->setName($this->username);
	}
	
	public function setCity($city) {
		$this->city = $city;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function setInfo1($info1) {
		$this->info1 = $info1;
	}
	
	public function setInfo2($info2) {
		$this->info2 = $info2;
	}
	
	public function setInfo3($info3) {
		$this->info3 = $info3;
	}

    public function __construct()
    {
        $this->favoriteEvents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userRoles = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add favoriteEvents
     *
     * @param TheScienceTour\EventBundle\Document\Event $favoriteEvents
     */
    public function addFavoriteEvent(\TheScienceTour\EventBundle\Document\Event $favoriteEvents)
    {
        $this->favoriteEvents[] = $favoriteEvents;
    }

    /**
    * Remove favoriteEvents
    *
    * @param <variableType$favoriteEvents
    */
    public function removeFavoriteEvent(\TheScienceTour\EventBundle\Document\Event $favoriteEvents)
    {
        $this->favoriteEvents->removeElement($favoriteEvents);
    }

    /**
     * Get favoriteEvents
     *
     * @return Doctrine\Common\Collections\Collection $favoriteEvents
     */
    public function getFavoriteEvents()
    {
        return $this->favoriteEvents;
    }
    
    public function addNotification($docType, $docId) {
    	$notif = new Notification($docType, $docId);
    	$this->notifications[] = $notif;
    }
    
    public function addBisNotification($notif) {
    	$this->notifications[] = $notif;
    }
    
    public function removeNotification($docType, $docId) {
    	$toDel = array();
    	foreach ($this->notifications as $notif) {
    		if ($notif->getDocId() == $docId && $notif->getDocType() == $docType) {
    			$toDel[] = $notif;
    		}
    	}
    	foreach ($toDel as $notif) {
    		$this->notifications->removeElement($notif);
    	}
    }
    
    public function numberOf($docType, $docId = null) {
    	$number = 0;
    	if (!$docId) {
    		foreach ($this->notifications as $notif) {
    			if (preg_match('#^'.$docType.'#', $notif->getDocType())) {                                
    				$number += 1;
    			}
    		}   		
    	} else {
    		foreach ($this->notifications as $notif) {
    			if ($notif->getDocId() == $docId && preg_match('#^'.$docType.'#', $notif->getDocType())) {
    				$number += 1;
    			}
    		}    		
    	}
    	return $number;
    }
    
    public function removeUselessNotifications($docType, $docIdList) {
    	$toDel = array();
    	foreach ($this->notifications as $notif) {
    		if (preg_match('#^'.$docType.'#', $notif->getDocType()) && !in_array($notif->getDocId(), $docIdList)) {
    			$toDel[] = $notif;
    		}
    	}
    	foreach ($toDel as $notif) {
    		$this->notifications->removeElement($notif);
    	}
    }
    
    public function addUserRole($userRole) {
    	$this->userRoles[] = $userRole;
    }
    
    public function removeUserRole($userRole) {
    	$this->userRoles->removeElement($userRole);
    }
}
