<?php
 
namespace TheScienceTour\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ColorPickerType extends AbstractType {
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array());
	}

	public function getName() {
		return 'colorpicker';
	}
	
	public function getParent() {
		return 'text';
	}
}
 