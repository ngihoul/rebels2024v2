<?php

namespace App\Repository;

use App\Entity\LicenseCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LicenseCategory>
 *
 * @method LicenseCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method LicenseCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method LicenseCategory[]    findAll()
 * @method LicenseCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenseCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenseCategory::class);
    }
}
