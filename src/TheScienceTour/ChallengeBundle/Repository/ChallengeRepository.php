<?php

namespace TheScienceTour\ChallengeBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class ChallengeRepository extends DocumentRepository {

	public function findAll() {
		return $this->createQueryBuilder()
			->sort('finishedAt', 'desc')
			->getQuery();
	}

	/**
	 * findInProgress Recherche les défis en cours de réalisation
	 * @param  boolean 			$isErasmus	Les projets Erasmus ou simplement du Science Tour ?
	 * @param  string			$locale		La langue par défaut de l'application
	 * @return ArrayCollection  		  	La liste des défis en cours
	 */
	public function findInProgress($isErasmus = false, $locale) {
		$now = new \DateTime();
		$builder = $this->createQueryBuilder();
		$query = $builder
			->field('startedAt')->lt($now)
			->field('finishedAt')->gt($now)
			// ->field('language')->like(':language')
			->addOr($builder->expr()->field('language')->equals($locale))
			->addOr($builder->expr()->field('language')->equals(null))
			//->setParameter('language', $locale)
			->sort('finishedAt', 'asc');
		if ($isErasmus) {
			$query->field('isErasmus')->equals(true);
		}

		return $query->getQuery();
	}

	/**
	 * findPast Recherche des défis terminés
	 * @param  boolean 			$isErasmus	Les projets Erasmus ou simplement du Science Tour ?
	 * @param  string			$locale		La langue par défaut de l'application
	 * @return ArrayCollection  		  	La liste des défis terminés
	 */
	public function findPast($isErasmus = false, $locale) {
$isErasmus = false;
		$query = $this->createQueryBuilder()
			->field('finishedAt')->lt(new \DateTime())
			->addOr($builder->expr()->field('language')->equals($locale))
			->addOr($builder->expr()->field('language')->equals(null))
			->sort('finishedAt', 'desc');
		if ($isErasmus) {
			$query->field('isErasmus')->equals(true);
		}

		return $query->getQuery();
	}

	/**
	 * findNonfuture Recherche des défis à venir
	 * @param  boolean 			$isErasmus	Les projets Erasmus ou simplement du Science Tour ?
	 * @param  string			$locale		La langue par défaut de l'application
	 * @return ArrayCollection  		  	La liste des défis à venir
	 */
	public function findNonfuture($isErasmus = false, $locale) {
		$query = $this->createQueryBuilder()
			->field('startedAt')->lt(new \DateTime())
			->sort('finishedAt', 'desc');
		if ($isErasmus) {
			$query->field('isErasmus')->equals(true);
		}

		return $query->getQuery();
	}

}
