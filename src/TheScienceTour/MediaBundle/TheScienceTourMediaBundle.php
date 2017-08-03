<?php

namespace TheScienceTour\MediaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TheScienceTourMediaBundle extends Bundle {
	
    public function getParent() {
        return 'SonataMediaBundle';
    }
}