<?php 

namespace TheScienceTour\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Ivory\GoogleMap\Places\AutocompleteType;

class ProjectAdmin extends Admin {

	protected function configureRoutes(RouteCollection $collection) {
		$collection
			->remove('create')
		;
	}
	
	protected function configureFormFields(FormMapper $formMapper) {
		$formMapper
			->add('title')
			->add('creator')
			->add('status', 'choice', array('choices' => array('0' => 'Brouillon', '1' => 'Publié')))
			->add('startedAt', 'date', array('label' => 'Starting date', 'empty_value' => '', 'required' => false))
			->add('duration')
			->add('durationUnit', 'choice', array('choices' => array('day' => 'Jours', 'week' => 'Semaines', 'month' => "Mois")))
			->add('price')
			->add('place', 'places_autocomplete', array(
				'prefix' => 'js_tst_place_',
				'types'  => array(AutocompleteType::CITIES),
				'async' => false,
				'language' => 'fr',
				'attr' => array('placeholder' => '', 'required' => 'required')
			))
			->add('picture', 'sonata_type_model_list', array(), array('link_parameters' => array(
					'context' => 'project')))
			->add('goal', 'textarea', array('label' => 'Goal of the game'))
			->add('description', 'textarea', array('label' => 'Rules of the game'))
			->add('frontPage', 'checkbox', array('required' => false))
		;

	}
	
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('title')
			->add('creator')
		;
	}
	
	protected function configureListFields(ListMapper $listMapper) {
		$listMapper
			->addIdentifier('title')
			->add('creator', null, array('sortable' => false))
			->add('status')
			->add('createdAt')
			->add('updatedAt')
			->add('publishedAt')
			->add('startedAt')
			->add('finishedAt')
			->add('place')
			->add('picture', null, array('sortable' => false))
			->add('duration')
			->add('durationUnit', null, array('sortable' => false))
			->add('price')
			->add('frontPage')
		;
	}
	
	public function preUpdate($project) {
		$project->updateFinishedAt();
	}
	
	public function getExportFields() {
		return array(
				'title',
				'creator',
				'status',
				'createdAt',
				'updatedAt',
				'publishedAt',
				'startedAt',
				'finishedAt',
				'place',
				'duration',
				'durationUnit',
				'price',
				'frontPage'
		);
	}
	
}
