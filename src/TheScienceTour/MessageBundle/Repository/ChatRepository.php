<?php

namespace TheScienceTour\MessageBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class ChatRepository extends DocumentRepository {
	
	public function findChatWithUser($iduser) {
		return $this->createQueryBuilder()
			->field('users.id')->equals($iduser)
			->sort('updatedAt', 'desc')
			->getQuery();
	}

}