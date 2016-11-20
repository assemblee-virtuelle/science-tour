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
		$prepare = $this->createQueryBuilder()
			->field('startedAt')->lt($now)
			->field('finishedAt')->gt($now)
			->sort('finishedAt', 'asc');
		if ($isErasmus) {
			$prepare->field('isErasmus')->equals(true);
		}
		$query = $prepare->getQuery();

		return $query->getResult();
	}

	public function findPast($isErasmus = false) {
		$prepare = $this->createQueryBuilder()
			->field('finishedAt')->lt(new \DateTime())
			->sort('finishedAt', 'desc');
		if ($isErasmus) {
			$prepare->field('isErasmus')->equals(true);
		}

		return $prepare->getQuery();
	}

	public function findNonfuture($isErasmus = false) {
		$prepare = $this->createQueryBuilder()
			->field('startedAt')->lt(new \DateTime())
			->sort('finishedAt', 'desc');
		if ($isErasmus) {
			$prepare->field('isErasmus')->equals(true);
		}

		return $prepare->getQuery();
	}

}
