<?php

namespace TheScienceTour\ChallengeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Ivory\GoogleMap\Places\AutocompleteType;

class ChallengeAdmin extends Admin {

	protected function configureRoutes(RouteCollection $collection) {
		$collection
			->remove('create')
		;
	}

	protected function configureFormFields(FormMapper $formMapper) {
		$formMapper
			->add('title')
			->add('creator')
			->add('startedAt', 'date', array('label' => 'Starting date', 'empty_value' => '', 'required' => false))
			->add('duration')
			->add('durationUnit', 'choice', array('choices' => array('day' => 'Jours', 'week' => 'Semaines', 'month' => "Mois")))
			->add('picture', 'sonata_type_model_list', array(), array('link_parameters' => array(
					'context' => 'challenge'))
			)
			->add('description', 'textarea', array('label' => 'Goal of the challenge'))
			->add('rules', 'textarea', array('label' => 'Rules of the challenge'))
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
			->add('createdAt')
			->add('updatedAt')
			->add('startedAt')
			->add('finishedAt')
			->add('picture', null, array('sortable' => false))
			->add('duration')
			->add('durationUnit', null, array('sortable' => false))
		;
	}

	public function preUpdate($project) {
		$project->updateFinishedAt();
	}

	public function getExportFields() {
		return array(
				'title',
				'creator',
				'createdAt',
				'updatedAt',
				'startedAt',
				'finishedAt',
				'duration',
				'durationUnit',
		);
	}
}