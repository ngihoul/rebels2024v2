<?php

namespace App\Repository;

use App\Entity\License;
use App\Entity\Payment;
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

    public function findWithPayments(string $licenseId): License
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->select('l', 'p')
            ->where('l.id = :licenseId')
            ->leftJoin('l.payments', 'p')
            ->setParameter('licenseId', $licenseId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
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

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getCurrentYearPendingLicense(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->select('l', 'p', 'po')
            ->leftJoin('l.payments', 'p', 'WITH', 'p.status IS NULL OR p.status != :paymentStatus')
            ->leftJoin('p.payment_orders', 'po')
            ->where('l.season = :currentYear')
            ->andWhere('l.status < :pendingStatus')
            ->andWhere('l.user = :user')
            ->setParameters([
                'currentYear' => date('Y'),
                'pendingStatus' => License::IN_ORDER,
                'user' => $user,
                'paymentStatus' => Payment::STATUS_REFUSED
            ])
            ->orderBy('po.due_date', 'ASC'); // Tri sur le champ due_date de PaymentOrder

        return $queryBuilder->getQuery()->getOneOrNullResult();
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
