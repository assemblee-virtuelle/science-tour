<?php 

namespace TheScienceTour\ProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SkillType extends AbstractType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', 'text', array('attr' => array('oninvalid' => 'javascript:show(2);', 'placeholder' => "Ex : Astrophysique, mécanique, chimie..."), 'label' => 'Name'))
			->add('number', 'integer', array('attr' => array('min' => 1, 'max' => 99, 'oninvalid' => 'javascript:show(2);'), 'label' => 'Quantity')
			     );
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
				'data_class' => 'TheScienceTour\ProjectBundle\Document\Skill'
		));
	}
	
	public function getName() {
		return 'project_skill';
	}
}