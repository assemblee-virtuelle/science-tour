<?php

namespace TheScienceTour\ChallengeBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Ivory\GoogleMap\Places\AutocompleteType;

/**
 * Classe concrète pour l'administration des objets du type "Challenge"
 *
 * Cette classe hérite de <strong>Sonata\AdminBundle\Admin\AbstractAdmin</strong> pour la gestion des objets Challenge
 * danbs l'interface d'administration <em>ad hoc</em>
 *
 * @package TheScienceTour\ChallengeBundle\Admin
 */
class ChallengeAdmin extends Admin {

    /**
     * configureRoutes
     *
     * Crée la liste des routes pour le menude l'interface d'administration
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
		$collection
			->remove('create')
		;
	}

    /**
     * configureFormFields
     *
     * @param FormMapper $formMapper
     */
	protected function configureFormFields(FormMapper $formMapper)
    {
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

    /**
     * configureDatagridFilters
     *
     * @param DatagridMapper $datagridMapper
     */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('title')
			->add('creator')
		;
	}

    /**
     * configureListFields
     *
     * @param ListMapper $listMapper
     */
	protected function configureListFields(ListMapper $listMapper)
    {
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

    /**
     * preUpdate
     *
     * @param $project
     */
	public function preUpdate($project)
    {
		$project->updateFinishedAt();
	}

    /**
     * getExportFields
     *
     * @return array
     */
	public function getExportFields() : array
    {
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
