<?php
namespace TheScienceTour\MapBundle\Helper;

use Ivory\GoogleMap\Helper\Overlays\MarkerHelper;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlays\Marker;

class TSTMarkerHelper extends MarkerHelper {
    public function render(Marker $marker, Map $map = null) {
        $this->jsonBuilder
            ->reset()
            ->setValue('[position]', $marker->getPosition()->getJavascriptVariable(), false);
        if ($map !== null) {
            $this->jsonBuilder->setValue('[map]', $map->getJavascriptVariable(), false);
        }
        if ($marker->hasAnimation()) {
            $this->jsonBuilder->setValue('[animation]', $this->animationHelper->render($marker->getAnimation()), false);
        }
        if ($marker->hasIcon()) {
            $this->jsonBuilder->setValue('[icon]', $marker->getIcon()->getJavascriptVariable(), false);
        }
        if ($marker->hasShadow()) {
            $this->jsonBuilder->setValue('[shadow]', $marker->getShadow()->getJavascriptVariable(), false);
        }
        if ($marker->hasShape()) {
            $this->jsonBuilder->setValue('[shape]', $marker->getShape()->getJavascriptVariable(), false);
        }
        $this->jsonBuilder->setValues($marker->getOptions());
        
        $infowindow = "";
        if ($marker->getInfoWindow()) {
        	$infowindow = sprintf('%s.infowindow = %s;'.PHP_EOL, $marker->getJavascriptVariable(), $marker->getInfoWindow()->getJavascriptVariable());
        }
        
        return sprintf(
            '%s = new google.maps.Marker(%s);%s'.PHP_EOL,
            $marker->getJavascriptVariable(),
            $this->jsonBuilder->build(),
        	$infowindow
        );
    }
}