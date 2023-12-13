<?php

namespace App\Repository;

use App\Entity\TrainingAttendee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrainingAttendee>
 *
 * @method TrainingAttendee|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrainingAttendee|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrainingAttendee[]    findAll()
 * @method TrainingAttendee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingAttendeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingAttendee::class);
    }

//    /**
//     * @return TrainingAttendee[] Returns an array of TrainingAttendee objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TrainingAttendee
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
