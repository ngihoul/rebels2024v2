<?php

namespace App\Repository;

use App\Entity\Payment;
use App\Entity\PaymentOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentOrder>
 */
class PaymentOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentOrder::class);
    }

    public function findPaymentOrdersToValidate(int $limit = null)
    {
        $queryBuilder = $this->createQueryBuilder('po')
            ->andWhere('po.value_date is NULL')
            ->orderBy('po.due_date', 'ASC');

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getPaymentsOverdue()
    {
        $today = new \DateTime();

        $queryBuilder = $this->createQueryBuilder('po')
            ->andWhere('po.value_date is NULL')
            ->andWhere('po.due_date < :date')
            ->setParameter('date', $today)
            ->orderBy('po.due_date', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function countPaymentOrdersToValidate()
    {
        $queryBuilder = $this->createQueryBuilder('po')
            ->select('COUNT(po.id)')
            ->andWhere('po.value_date is NULL');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
