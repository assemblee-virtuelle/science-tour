<?php 

namespace TheScienceTour\ProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResourceType extends AbstractType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', 'text', array('attr' => array('oninvalid' => 'javascript:show(2);', 'placeholder' => ''), 'label' => 'Name'))
			->add('number', 'integer', array('attr' => array('min' => 1, 'max' => 99, 'oninvalid' => 'javascript:show(2);'), 'label' => false))
			->add('creatorHelpNb', 'integer', array('attr' => array('min' => 0, 'max' => 99, 'oninvalid' => 'javascript:show(2);'), 'label' => false))
			;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
				'data_class' => 'TheScienceTour\ProjectBundle\Document\Resource'
		));
	}
	
	public function getName() {
		return 'project_resource';
	}
}

