<?php 

namespace TheScienceTour\MapBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */

class ProvidedAddress extends Constraint {
	
	public $message = 'Cette adresse n\'est pas valide';
	
	public function validatedBy()
	{
		return 'the_science_tour_map.validator.provided_address';
	}

}