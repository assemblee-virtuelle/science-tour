<?php


namespace TheScienceTour\UserBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

use FOS\UserBundle\Model\UserManagerInterface;

class UserAdmin extends Admin {

	protected function configureRoutes(RouteCollection $collection) {
		$collection
			->remove('create')
		;
	}

	protected function configureFormFields(FormMapper $formMapper) {
		$formMapper
		->add('username')
		->add('usernameCanonical')
		->add('email')
		->add('emailCanonical')
		->add('city', null, array('required' => false))
		->add('enabled', null, array('required' => false))
		->add('expired', null, array('required' => false))
		->add('locked', null, array('required' => false))
		->add('roles', 'choice', array('choices' => array(
			"ROLE_SUPER_ADMIN" => "ROLE_SUPER_ADMIN",
			"ROLE_ADMIN" => "ROLE_ADMIN",
			"ROLE_SUPER_ANIM" => "ROLE_SUPER_ANIM",
			"ROLE_PROJECT_MOD" => "ROLE_PROJECT_MOD",
			"ROLE_RESEARCHER" => "ROLE_RESEARCHER",
			"ROLE_USER" => "ROLE_USER"
			),
			'required' => true,
			'multiple' => true
		))
		;
	}

	protected function configureListFields(ListMapper $listMapper) {
		$listMapper
		->addIdentifier('username')
		->add('avatar', null, array('template' => 'TheScienceTourUserBundle:Admin:avatar.html.twig'))
		->add('email')
		->add('city')
		->add('enabled')
		->add('expired')
		->add('lastLogin')
		->add('locked')
		->add('roles')
		;
	}

	public function setUserManager(UserManagerInterface $userManager) {
		$this->userManager = $userManager;
	}

	public function getUserManager() {
		return $this->userManager;
	}

	public function getExportFields() {
		return array('id', 'username', 'usernameCanonical', 'email', 'emailCanonical', 'avatar', 'description', 'city', 'enabled', 'password', 'lastLogin');
	}
}
