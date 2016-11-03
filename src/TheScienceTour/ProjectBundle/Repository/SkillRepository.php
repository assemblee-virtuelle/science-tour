<?php

namespace TheScienceTour\ProjectBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class SkillRepository extends DocumentRepository {
	
	public function findSkillsWithHelper($iduser) {
		return $this->createQueryBuilder()
			->field('helpers.id')->equals($iduser)
			->getQuery();
	}
	
}