<?php

namespace TheScienceTour\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TheScienceTourUserBundle extends Bundle {
	
	public function getParent() {
		return 'FOSUserBundle';
	}
}
