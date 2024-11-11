<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findPaymentPlanRequestsToValidate(int $limit = null)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p', 'l')
            ->leftJoin('p.license', 'l')
            ->andWhere('p.status is NULL')
            ->andWhere(('p.payment_type = :type'))
            ->orderBy('p.created_at', 'DESC')
            ->setParameter('type', Payment::BY_PAYMENT_PLAN);

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function countPaymentPlanRequestsToValidate()
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status is NULL')
            ->andWhere(('p.payment_type = :type'))
            ->setParameter('type', Payment::BY_PAYMENT_PLAN);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
