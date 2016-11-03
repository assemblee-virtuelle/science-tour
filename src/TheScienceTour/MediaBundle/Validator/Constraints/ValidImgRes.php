<?php 

namespace TheScienceTour\MediaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */

class ValidImgRes extends Constraint {
	
	public $message = 'La résolution de l\'image doit être inférieure à 4000 x 4000 pixels.';
	

}