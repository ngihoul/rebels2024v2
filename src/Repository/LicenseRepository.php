<?php

namespace App\Repository;

use App\Entity\License;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<License>
 *
 * @method License|null find($id, $lockMode = null, $lockVersion = null)
 * @method License|null findOneBy(array $criteria, array $orderBy = null)
 * @method License[]    findAll()
 * @method License[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, License::class);
    }

    public function getCurrentYearActiveLicense(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.season = :currentYear')
            ->andWhere('l.status = :activeStatus')
            ->andWhere('l.user = :user')
            ->setParameters([
                'currentYear' => date('Y'),
                'activeStatus' => License::IN_ORDER,
                'user' => $user,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getCurrentYearPendingLicenses(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.season = :currentYear')
            ->andWhere('l.status < :pendingStatus')
            ->andWhere('l.user = :user')
            ->setParameters([
                'currentYear' => date('Y'),
                'pendingStatus' => License::IN_ORDER,
                'user' => $user,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getPastYearsLicenses(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.season < :currentYear')
            ->andWhere('l.user = :user')
            ->setParameters([
                'currentYear' => date('Y'),
                'user' => $user,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllLicensesToValidate()
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->setParameter('status', License::DOC_RECEIVED);

        return $queryBuilder->getQuery()->getResult();
    }
}
