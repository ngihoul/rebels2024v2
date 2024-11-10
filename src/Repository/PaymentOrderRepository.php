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

    public function findPaymentOrdersToValidate(int $limit)
    {
        $queryBuilder = $this->createQueryBuilder('po')
            ->andWhere('po.value_date is NULL')
            ->setMaxResults($limit)
            ->orderBy('po.due_date', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }
}
