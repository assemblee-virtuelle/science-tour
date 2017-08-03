<?php

namespace TheScienceTour\ProjectBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */

class AtLeastOneRes extends Constraint {

	public $message = 'The project requires at least one tool or one material to be published.';

	public function getTargets() {
		return self::CLASS_CONSTRAINT;
	}
}
