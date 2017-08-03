<?php
namespace TheScienceTour\EventBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use TheScienceTour\MainBundle\Form\Type\ColorPickerType;

class LabelAdmin extends Admin
{
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->add('title')
		->add('labelType', 'sonata_type_model_list', array('required' => false))
		->add('backgroundColor', new ColorPickerType(), array('required' => false))
		;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('title')
		->add('labelType')
		->add('backgroundColor')
		;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('title')
		->add('labelType')
		->add('backgroundColor')
		;
	}
}
