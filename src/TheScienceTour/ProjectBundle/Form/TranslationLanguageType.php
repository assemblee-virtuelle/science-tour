<?php

namespace TheScienceTour\ProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TranslationLanguageType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->setAttribute('languages_set', $options['data']);

		$builder->add('language', 'choice', [
          'choices'           => $builder->getAttribute('languages_set'),
        //   'preferred_choices' => $builder->getAttribute('no_choice'),
          'multiple'          => false,
          'expanded'          => false
        ]);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
				'data_class' => 'TheScienceTour\ProjectBundle\Document\ProjectTranslation',
				'languages_set' => ['fr', 'en'],
				'no_choice' => '-- Translate to... --'
		));
	}

	public function getName() {
		return 'project_translation_language';
	}
}
