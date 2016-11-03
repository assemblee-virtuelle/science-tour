<?php
namespace TheScienceTour\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use TheScienceTour\MainBundle\Form\Type\ColorPickerType;

class LabelTypeAdmin extends Admin
{
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->add('name')
		->add('slug')
		->add('marker', 'sonata_type_model_list', array('required' => false), array('link_parameters' => array(
			'context' => 'marker'), 'placeholder' => 'No marker selected'))
		->add('markerFormat', 'choice', array(
			'choices' => array('small' => 'small', 'medium' => 'medium', 'big' => 'big')))
		->add('picture', 'sonata_type_model_list', array('required' => false), array('link_parameters' => array(
			'context' => 'event'), 'placeholder' => 'No picture selected'))
		;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('name')
		->add('slug')
		;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('name')
		->add('slug')
		->add('marker')
		->add('picture')
		;
	}
	
	/* (non-PHPdoc)
	 * @see \Sonata\AdminBundle\Admin\Admin::prePersist()
	 */
	public function prePersist($object) {
		$this->preUpdate($object);
	}
	
	/* (non-PHPdoc)
	 * @see \Sonata\AdminBundle\Admin\Admin::preUpdate()
	 */
	public function preUpdate($object) {
		$object->setMarker($object->getMarker());
	}
}
