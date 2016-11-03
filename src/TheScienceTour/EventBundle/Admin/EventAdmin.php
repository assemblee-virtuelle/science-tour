<?php
namespace TheScienceTour\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Ivory\GoogleMap\Places\AutocompleteType;

class EventAdmin extends Admin
{
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->add('title')
		->add('bidullActivityId', null, array('required' => false))
		->add('description', 'textarea')
		->add('startDate')
		->add('endDate')
		->add('place', 'places_autocomplete', array(
			'prefix' => 'js_tst_place_',
			'types'  => array(AutocompleteType::CITIES),
			'async' => false,
			'language' => 'fr',
			'attr' => array('placeholder' => '', 'required' => 'required')
		))
		->add('picture', 'sonata_type_model_list', array('required' => false), array('link_parameters' => array(
			'context' => 'event')))
		->add('label', 'sonata_type_model_list', array('required' => false))
		->add('frontPage', 'choice', array(
			'choices' => array(0 => 'no', 1 => 'yes')
		))
		;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('title')
		->add('bidullActivityId')
		->add('description')
		;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('title')
		->add('label', null, array('template' => 'TheScienceTourEventBundle:Admin:label_with_labeltype.html.twig'))
		//->add('label')
		//->add('label.labelType')
		->add('bidullActivityId')
		->add('startDate')
		->add('endDate')
		->add('frontPage')
		;
	}
	
	public function preUpdate($object)
	{
		// If the place has change then clear the coordinates
		// so they are updated in MapBundle\Listener\CoordinatesSetterSubscriber
		$em = $this->getModelManager()->getEntityManager($this->getClass());
		$original = $em->getUnitOfWork()->getOriginalDocumentData($object);
		if ($original['place'] != $object->getPlace()) {
			$object->unsetCoordinates();
		}
	}
}
