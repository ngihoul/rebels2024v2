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

    public function findPaymentPlanRequestToValidate(int $limit)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p', 'l')
            ->leftJoin('p.license', 'l')
            ->andWhere('p.status is NULL')
            ->andWhere(('p.payment_type = :type'))
            ->setMaxResults($limit)
            ->orderBy('p.created_at', 'DESC')
            ->setParameter('type', Payment::BY_PAYMENT_PLAN);

        return $queryBuilder->getQuery()->getResult();
    }
}
