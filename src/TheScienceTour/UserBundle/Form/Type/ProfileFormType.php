<?php 

namespace TheScienceTour\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ivory\GoogleMap\Places\AutocompleteType;

class ProfileFormType extends BaseType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('email', 'email')
			->add('avatar', 'sonata_media_type', array(
					'provider'	=> 'sonata.media.provider.image',
					'context'	=> 'user',
					'required' => false,
					'label' => 'Avatar'
			))
			->add('description', 'textarea', array('label' => 'Description', 'required' => false))
			->add('city', 'places_autocomplete', array(
					'prefix' => 'js_tst_place_',
					'types'  => array(AutocompleteType::CITIES),
					'async' => false,
					'language' => 'fr',
					'label' =>'Ville',
					'required' => false,
					'attr' => array('placeholder' => '', 'class' => 'w80')
			))
		;
	}
	
	public function getName() {
		return 'tst_user_profile';
	}
}