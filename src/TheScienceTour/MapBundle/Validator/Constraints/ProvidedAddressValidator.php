<?php 

namespace TheScienceTour\MapBundle\Validator\Constraints;

use TheScienceTour\MapBundle\Helper\MapHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProvidedAddressValidator extends ConstraintValidator {
	
	protected $mapHelper;
	
	public function __construct(MapHelper $mapHelper) {
		$this->mapHelper = $mapHelper;
	}

	public function validate($value, Constraint $constraint) {
		try {
			$geocode = $this->mapHelper->getGeocode($value);
		} catch (\Geocoder\Exception\ExceptionInterface $e) {
			$this->context->addViolation($constraint->message);
		}

	}
}