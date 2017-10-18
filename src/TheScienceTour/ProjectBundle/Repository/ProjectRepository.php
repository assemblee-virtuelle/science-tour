<?php

namespace TheScienceTour\ProjectBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use TheScienceTour\MainBundle\Model\GeoNear;
use Doctrine\MongoDB\Query\Builder;

class ProjectRepository extends DocumentRepository {

  private function erasmusQueryFilter(Builder $queryBuilder) {
    // Can be used by reference or not.
    return $queryBuilder;
  }

  public function findFrontPage() {
    // Start query.
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->sort(array(
        'frontPage' => 'desc',
        'updatedAt' => 'desc'
      ))
      ->limit(9);

    return $query->getQuery();
  }

  public function findInProgress(GeoNear $geoNear = NULL) {
    $now   = new \DateTime();
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->field('startedAt')->lt($now)
      ->field('finishedAt')->gt($now);

    if ($geoNear) {
      $query = $geoNear->addToQueryBuilder($query);
    }

    return $query->sort('startedAt', 'asc')
      ->getQuery();
  }

  public function findFinished(GeoNear $geoNear = NULL) {
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->field('finishedAt')->lt(new \DateTime());

    if ($geoNear) {
      $query = $geoNear->addToQueryBuilder($query);
    }

    return $query->sort('finishedAt', 'desc')
      ->getQuery();
  }

  public function findFinishedSoon(GeoNear $geoNear = NULL) {
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

    return $query->sort('finishedAt', 'asc')
      ->getQuery();
  }

  public function findLastPublished() {
    return $this->createQueryBuilder()
      ->field('status')->notEqual(0)
      ->sort('publishedAt', 'desc')
      ->getQuery();
  }

  public function findLastUpdated(GeoNear $geoNear = NULL) {
    $query = $this->createQueryBuilder()
      ->field('status')->notEqual(0);

    if ($geoNear) {
      $query = $geoNear->addToQueryBuilder($query);
    }

    return $query->sort('updatedAt', 'desc')
      ->getQuery();
  }

  public function findProjectsCreatedBy($idUser) {
    return ($this->createQueryBuilder()
                 ->field('status')->notEqual(0)
                 ->field('creator.id')->equals($idUser)
                 ->sort('publishedAt', 'desc'))
            ->getQuery();
  }

  public function findDraftsCreatedBy($idUser) {
    return ($this->createQueryBuilder()
                 ->field('status')->equals(0)
                 ->field('creator.id')->equals($idUser)
                 ->sort('createdAt', 'desc'))
            ->getQuery();
  }

  public function findProjectsWithContributor($idUser) {
    return ($this->createQueryBuilder()
                 ->field('status')->notEqual(0)
                 ->field('contributors.id')->equals($idUser)
                 ->sort('publishedAt', 'desc'))
            ->getQuery();
  }

  public function findProjectsWithContributorOrSkills($idUser, $idskills) {
    $qb = $this->createQueryBuilder();
    $qb->field('status')->notEqual(0)
       ->addOr($qb->expr()->field('contributors.id')->equals($idUser))
       ->addOr($qb->expr()->field('skills.id')->in($idskills))
       ->sort('publishedAt', 'desc');

    return $qb->getQuery();
  }

  public function findProjectsWithSubscriber($idUser) {
    return ($this->createQueryBuilder()
                 ->field('status')->notEqual(0)
                 ->field('subscribers.id')->equals($idUser)
                 ->sort('publishedAt', 'desc'))
            ->getQuery();
  }

  public function findProjectsWithSupporter($idUser) {
    return ($this->createQueryBuilder()
                 ->field('status')->notEqual(0)
                 ->field('supporters.id')->equals($idUser)
                 ->sort('publishedAt', 'desc'))
            ->getQuery();
  }

  public function findProjectsWithSponsor($idUser) {
    return ($this->createQueryBuilder()
                 ->field('status')->notEqual(0)
                 ->field('challenge.id')->exists(TRUE)
                 ->field('sponsors.id')->equals($idUser)
                 ->sort('publishedAt', 'desc'))
            ->getQuery();
  }

  public function findGeoNear($latitude, $longitude, $maxDistance = 50) {
    // Convert from/to radians with 6378.137 for km and 3963.192 miles
    $radius      = 6378.137;
    $maxDistance = $maxDistance / $radius;

    return $this->createQueryBuilder()
                ->field('status')->notEqual(0)
                ->field('coordinates')->geoNear($longitude, $latitude)
                ->maxDistance($maxDistance)
                ->spherical(TRUE)
                ->distanceMultiplier($radius, true) //@TODO Vérification de la syntaxe
            ->getQuery();
  }
}
