<?php

namespace App\Repository;

use App\Entity\Relation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Relation>
 */
class RelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Relation::class);
    }

    // GetRelation where parent is $parent and child is $child
    public function getRelation($parent, $child): ?Relation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.parent = :parent')
            ->andWhere('r.child = :child')
            ->setParameter('parent', $parent)
            ->setParameter('child', $child)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
