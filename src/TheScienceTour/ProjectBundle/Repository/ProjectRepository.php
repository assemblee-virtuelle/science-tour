<?php

namespace TheScienceTour\ProjectBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use TheScienceTour\MainBundle\Model\GeoNear;

class ProjectRepository extends DocumentRepository {

	public function findFrontPage($isErasmus) {
		// Start query.
		$query = $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->sort(array(
					'frontPage' => 'desc',
					'updatedAt' => 'desc'
			))
			->limit(9);
		// For erasmus version.
		if ($isErasmus) {
			// Only erasmus projects.
			$query->field('isErasmus')->equals(true);
		}

		return $query->getQuery();
	}

	public function findInProgress(GeoNear $geoNear = null) {
		$now = new \DateTime();
		$qb = $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('startedAt')->lt($now)
			->field('finishedAt')->gt($now);

		if ($geoNear) {
			$qb = $geoNear->addToQueryBuilder($qb);
		}

		return $qb->sort('startedAt', 'asc')
			->getQuery();
	}

	public function findFinished(GeoNear $geoNear = null) {
		$qb = $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('finishedAt')->lt(new \DateTime());

		if ($geoNear) {
			$qb = $geoNear->addToQueryBuilder($qb);
		}

		return $qb->sort('finishedAt', 'desc')
			->getQuery();
	}

	public function findFinishedSoon(GeoNear $geoNear = null) {
		$now = new \DateTime();
		$soon = clone $now;
		$soon->add(new \DateInterval('P10D'));
		$qb = $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('startedAt')->lt($now)
			->field('finishedAt')->gt($now)
			->field('finishedAt')->lt($soon);

		if ($geoNear) {
			$qb = $geoNear->addToQueryBuilder($qb);
		}

		return $qb->sort('finishedAt', 'asc')
			->getQuery();
	}

	public function findLastPublished() {
		return $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->sort('publishedAt', 'desc')
			->getQuery();
	}

	public function findLastUpdated(GeoNear $geoNear = null) {
		$qb = $this->createQueryBuilder()
			->field('status')->notEqual(0);

		if ($geoNear) {
			$qb = $geoNear->addToQueryBuilder($qb);
		}

		return $qb->sort('updatedAt', 'desc')
			->getQuery();
	}

	public function findProjectsCreatedBy($iduser) {
		return $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('creator.id')->equals($iduser)
			->sort('publishedAt', 'desc')
			->getQuery();
	}

	public function findDraftsCreatedBy($iduser) {
		return $this->createQueryBuilder()
			->field('status')->equals(0)
			->field('creator.id')->equals($iduser)
			->sort('createdAt', 'desc')
			->getQuery();
	}

	public function findProjectsWithContributor($iduser) {
		return $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('contributors.id')->equals($iduser)
			->sort('publishedAt', 'desc')
			->getQuery();
	}

	public function findProjectsWithContributorOrSkills($iduser, $idskills) {
		$qb = $this->createQueryBuilder();
		$qb->field('status')->notEqual(0)
			->addOr($qb->expr()->field('contributors.id')->equals($iduser))
			->addOr($qb->expr()->field('skills.id')->in($idskills))
			->sort('publishedAt', 'desc');
		return $qb->getQuery();
	}

	public function findProjectsWithSubscriber($iduser) {
		return $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('subscribers.id')->equals($iduser)
			->sort('publishedAt', 'desc')
			->getQuery();
	}

	public function findProjectsWithSupporter($iduser) {
		return $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('supporters.id')->equals($iduser)
			->sort('publishedAt', 'desc')
			->getQuery();
	}

	public function findProjectsWithSponsor($iduser) {
		return $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('challenge.id')->exists(true)
			->field('sponsors.id')->equals($iduser)
			->sort('publishedAt', 'desc')
			->getQuery();
	}

	public function findGeoNear($latitude, $longitude, $maxDistance = 50) {
		// Convert from/to radians with 6378.137 for km and 3963.192 miles
		$radius = 6378.137;
		$maxDistance = $maxDistance / $radius;

		return $this->createQueryBuilder()
			->field('status')->notEqual(0)
			->field('coordinates')->geoNear($longitude, $latitude)
			->maxDistance($maxDistance)
			->spherical(true)
			->distanceMultiplier($radius)
			->getQuery();
	}
}
