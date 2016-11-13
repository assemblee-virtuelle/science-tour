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
	 * @return ArrayCollection  		  	La liste des résultats de recherche
	 */
	public function findInProgress($isErasmus = false) {
		$now = new \DateTime();
		return $this->createQueryBuilder()
			->field('startedAt')->lt($now)
			->field('finishedAt')->gt($now)
			->field('isErasmus')->equals($isErasmus)
			->sort('finishedAt', 'asc')
			->getQuery();
	}

	public function findPast($isErasmus = false) {
		return $this->createQueryBuilder()
			->field('finishedAt')->lt(new \DateTime())
			->field('isErasmus')->equals($isErasmus)
			->sort('finishedAt', 'desc')
			->getQuery();
	}

	public function findNonfuture($isErasmus = false) {
		return $this->createQueryBuilder()
			->field('startedAt')->lt(new \DateTime())
			->field('isErasmus')->equals($isErasmus)
			->sort('finishedAt', 'desc')
			->getQuery();
	}

}
