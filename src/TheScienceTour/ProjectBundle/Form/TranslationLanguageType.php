<?php

namespace TheScienceTour\ProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TranslationLanguageType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		// $builder->setAttribute('data', $options['data']);

		$builder->add('language', 'choice', [
          'choices'      => ['fr' => 'Français', 'en' => 'English'],
          'empty_value'  => $builder->getAttribute('empty_value'),
          'multiple'     => false,
          'expanded'     => false
        ]);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
				'data_class' => 'TheScienceTour\ProjectBundle\Document\ProjectTranslation',
				'empty_value' => '-- Translate to... --'
		));
	}

	public function getName() {
		return 'project_translation_language';
	}
}
