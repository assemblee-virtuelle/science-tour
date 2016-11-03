<?php 

namespace TheScienceTour\MediaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */

class ValidImgSize extends Constraint {
	
	public $message = 'La taille de l\'image doit être inférieure à 2 Mo.';
	

}