<?php

namespace TheScienceTour\MapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ivory\GoogleMap\Places\AutocompleteType;
use Symfony\Component\CssSelector\XPath\Translator;

class MapFilterGeoNearController extends Controller {

	public function defaultAction($route) {
		$form = $this->createFormBuilder()
			->add('center', 'places_autocomplete', array(
				'prefix' => 'js_tst_map_center_',
				'types'  => array(AutocompleteType::CITIES),
				'async' => false,
				'language' => 'fr',
				'data' => (in_array($route['parameters']['center'], array('around-me', 'all'))) ? '' : $route['parameters']['center'],
				'attr' => array(
						'placeholder' => $this->get('translator')->trans('Place'),
						'oninvalid' => 'javascript:show(0);',
						'required' => 'required'
				)
			))
			->getForm();

		return $this->render('TheScienceTourMapBundle::mapFilterGeoNear.html.twig', array(
				'route' => $route,
				'form' => $form->createView()
		));
	}

}
