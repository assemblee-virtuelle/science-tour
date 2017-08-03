<?php

namespace TheScienceTour\ProjectBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use TheScienceTour\ProjectBundle\Document\Project;

class AtLeastOneResValidator extends ConstraintValidator {

  public function validate($project, Constraint $constraint) {
    if ($project instanceof Project && !$project->getIsErasmus() &&
      (!$project->getTools() || $project->getTools()->count() < 1) &&
      (!$project->getMaterials() || $project->getMaterials()->count() < 1)
    ) {
      $this->context->addViolation($constraint->message);
    }
  }
}
