<?php 

namespace TheScienceTour\MediaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidImgResValidator extends ConstraintValidator {
	
	public function validate($value, Constraint $constraint) {
		if ($value != null) {
			if ($value->getWidth() > 4000 || $value->getHeight() > 4000) {
				$this->context->addViolation($constraint->message);
			}
		}
	}
	
}
