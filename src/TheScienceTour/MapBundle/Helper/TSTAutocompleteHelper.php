<?php
namespace TheScienceTour\MapBundle\Helper;

use Ivory\GoogleMap\Helper\Places\AutocompleteHelper;
use Ivory\GoogleMap\Places\Autocomplete;

class TSTAutocompleteHelper extends AutocompleteHelper {
	public function renderAutocomplete(Autocomplete $autocomplete) {
		$this->jsonBuilder->reset();
		if ($autocomplete->hasTypes()) {
			$this->jsonBuilder->setValue('[types]', $autocomplete->getTypes());
		}
		if ($autocomplete->hasBound()) {
			$this->jsonBuilder->setValue('[bounds]', $autocomplete->getBound()->getJavascriptVariable(), false);
		}
		if (!$this->jsonBuilder->hasValues()) {
			$this->jsonBuilder->setJsonEncodeOptions(JSON_FORCE_OBJECT);
		}
			return sprintf(
				'autocomplete = %s = new google.maps.places.Autocomplete(document.getElementById(\'%s\', %s));'.PHP_EOL,
				$autocomplete->getJavascriptVariable(),
				$autocomplete->getInputId(),
				$this->jsonBuilder->build()
		);
	}
}