<?php

namespace Erasmus\MainBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ErasmusMainBundle extends Bundle
{
  public function getParent() {
    return 'TheScienceTourMainBundle';
  }
}
