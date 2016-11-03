<?php

namespace TheScienceTour\ChallengeBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class ChallengeRepository extends DocumentRepository {
	
	public function findAll() {
		return $this->createQueryBuilder()
			->sort('finishedAt', 'desc')
			->getQuery();
	}
	
	public function findInProgress() {
		$now = new \DateTime();
		return $this->createQueryBuilder()
			->field('startedAt')->lt($now)
			->field('finishedAt')->gt($now)
			->sort('finishedAt', 'asc')
			->getQuery();
	}
	
	public function findPast() {
		return $this->createQueryBuilder()
			->field('finishedAt')->lt(new \DateTime())
			->sort('finishedAt', 'desc')
			->getQuery();
	}
	
	public function findNonfuture() {
		return $this->createQueryBuilder()
			->field('startedAt')->lt(new \DateTime())
			->sort('finishedAt', 'desc')
			->getQuery();
	}
	
}