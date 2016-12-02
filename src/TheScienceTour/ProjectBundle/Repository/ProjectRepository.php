<?php

namespace TheScienceTour\ProjectBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use TheScienceTour\MainBundle\Model\GeoNear;
use Doctrine\MongoDB\Query\Builder;

class ProjectRepository extends DocumentRepository {

  private function erasmusQueryFilter(Builder $queryBuilder, $isErasmus) {
    // For erasmus version.
    if ($isErasmus) {
      // Only erasmus projects.
      $queryBuilder->field('isErasmus')->equals(TRUE);
    }
    // Can be used by reference or not.
    return $queryBuilder;
  }

  public function findFrontPage($isErasmus) {
    // Start query.
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->sort(array(
        'frontPage' => 'desc',
        'updatedAt' => 'desc'
      ))
      ->limit(9);
    // Show erasmus only or not.
    $this->erasmusQueryFilter($query, $isErasmus);

    return $query->getQuery();
  }

  public function findInProgress(GeoNear $geoNear = NULL, $isErasmus) {
    $now   = new \DateTime();
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->field('startedAt')->lt($now)
      ->field('finishedAt')->gt($now);

    if ($geoNear) {
      $query = $geoNear->addToQueryBuilder($query);
    }

    // Show erasmus only or not.
    $this->erasmusQueryFilter($query, $isErasmus);

    return $query->sort('startedAt', 'asc')
      ->getQuery();
  }

  public function findFinished(GeoNear $geoNear = NULL, $isErasmus) {
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->field('finishedAt')->lt(new \DateTime());

    if ($geoNear) {
      $query = $geoNear->addToQueryBuilder($query);
    }

    // Show erasmus only or not.
    $this->erasmusQueryFilter($query, $isErasmus);

    return $query->sort('finishedAt', 'desc')
      ->getQuery();
  }

  public function findFinishedSoon(GeoNear $geoNear = NULL, $isErasmus) {
    $now  = new \DateTime();
    $soon = clone $now;
    $soon->add(new \DateInterval('P10D'));
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->field('startedAt')->lt($now)
      ->field('finishedAt')->gt($now)
      ->field('finishedAt')->lt($soon);

    if ($geoNear) {
      $query = $geoNear->addToQueryBuilder($query);
    }

    // Show erasmus only or not.
    $this->erasmusQueryFilter($query, $isErasmus);

    return $query->sort('finishedAt', 'asc')
      ->getQuery();
  }

  public function findLastPublished() {
    return $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->sort('publishedAt', 'desc')
      ->getQuery();
  }

  public function findLastUpdated(GeoNear $geoNear = NULL, $isErasmus) {
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0);

    if ($geoNear) {
      $query = $geoNear->addToQueryBuilder($query);
    }

    // Show erasmus only or not.
    $this->erasmusQueryFilter($query, $isErasmus);

    return $query->sort('updatedAt', 'desc')
      ->getQuery();
  }

  public function findProjectsCreatedBy($idUser, $isErasmus) {
    return $this->erasmusQueryFilter($this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->field('creator.id')->equals($idUser)
      ->sort('publishedAt', 'desc'), $isErasmus)->getQuery();
  }

  public function findDraftsCreatedBy($idUser, $isErasmus) {
    return $this->erasmusQueryFilter($this->createQueryBuilder()
      ->field('status')->equals(0)
      ->field('creator.id')->equals($idUser)
      ->sort('createdAt', 'desc'), $isErasmus)->getQuery();
  }

  public function findProjectsWithContributor($idUser, $isErasmus) {
    return $this->erasmusQueryFilter($this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->field('contributors.id')->equals($idUser)
      ->sort('publishedAt', 'desc'), $isErasmus)->getQuery();
  }

  public function findProjectsWithContributorOrSkills($idUser, $idskills, $isErasmus) {
    $qb = $this->createQueryBuilder();
    $qb->field('status')->notEqual(0)
      ->addOr($qb->expr()->field('contributors.id')->equals($idUser))
      ->addOr($qb->expr()->field('skills.id')->in($idskills))
      ->sort('publishedAt', 'desc');
    return $this->erasmusQueryFilter($qb, $isErasmus)->getQuery();
  }

  public function findProjectsWithSubscriber($idUser, $isErasmus) {
    return $this->erasmusQueryFilter(
      $this->createQueryBuilder()
        ->field('status')->notEqual(0)
        ->field('subscribers.id')->equals($idUser)
        ->sort('publishedAt', 'desc'), $isErasmus)->getQuery();
  }

  public function findProjectsWithSupporter($idUser, $isErasmus) {
    return $this->erasmusQueryFilter(
      $this->createQueryBuilder()
        ->field('status')->notEqual(0)
        ->field('supporters.id')->equals($idUser)
        ->sort('publishedAt', 'desc'), $isErasmus)->getQuery();
  }

  public function findProjectsWithSponsor($idUser, $isErasmus) {
    return $this->erasmusQueryFilter(
      $this->createQueryBuilder()
        ->field('status')->notEqual(0)
        ->field('challenge.id')->exists(TRUE)
        ->field('sponsors.id')->equals($idUser)
        ->sort('publishedAt', 'desc'), $isErasmus)->getQuery();
  }

  public function findGeoNear($latitude, $longitude, $maxDistance = 50, $isErasmus) {
    // Convert from/to radians with 6378.137 for km and 3963.192 miles
    $radius      = 6378.137;
    $maxDistance = $maxDistance / $radius;

    return $this->erasmusQueryFilter($this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->field('coordinates')->geoNear($longitude, $latitude)
      ->maxDistance($maxDistance)
      ->spherical(TRUE)
      ->distanceMultiplier($radius), $isErasmus)
      ->getQuery();
  }
}
