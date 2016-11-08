<?php

namespace Erasmus\ChallengeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ErasmusChallengeBundle extends Bundle
{
	public function getParent() {
		
		return 'TheScienceTourChallengeBundle';
	}
}
