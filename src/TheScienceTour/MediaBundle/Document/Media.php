<?php

namespace TheScienceTour\MediaBundle\Document;

use Sonata\MediaBundle\Document\BaseMedia as BaseMedia;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Media extends BaseMedia {

	/**
	 * @MongoDB\Id
	 */
    protected $id;

    public function getId() {
        return $this->id;
    }
}