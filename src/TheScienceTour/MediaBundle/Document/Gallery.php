<?php

namespace TheScienceTour\MediaBundle\Document;

use Sonata\MediaBundle\Document\BaseGallery as BaseGallery;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Gallery extends BaseGallery {
	
	/**
	 * @MongoDB\Id
	 */
    protected $id;

    public function getId() {
        return $this->id;
    }
}