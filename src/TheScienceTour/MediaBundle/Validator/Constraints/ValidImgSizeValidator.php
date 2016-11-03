<?php 

namespace TheScienceTour\MediaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidImgSizeValidator extends ConstraintValidator {
	
	public function validate($value, Constraint $constraint) {
		if ($value != null) {
			if ($value->getSize() > 2097152) {
				$this->context->addViolation($constraint->message);
			}
		}
	}
}