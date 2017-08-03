<?php

namespace TheScienceTour\ProjectBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class NewsRepository extends DocumentRepository {
	
	public function findOrderByDate($projectId) {
		return $this->createQueryBuilder()
			->field('projectId')->equals($projectId)
			->sort('startedAt', 'desc')
			->sort('id', 'desc')
			->getQuery()
			->execute();
	}

}