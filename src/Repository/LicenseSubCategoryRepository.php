<?php

namespace App\Repository;

use App\Entity\LicenseSubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LicenseSubCategory>
 *
 * @method LicenseSubCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method LicenseSubCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method LicenseSubCategory[]    findAll()
 * @method LicenseSubCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenseSubCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenseSubCategory::class);
    }

//    /**
//     * @return LicenseSubCategory[] Returns an array of LicenseSubCategory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LicenseSubCategory
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
