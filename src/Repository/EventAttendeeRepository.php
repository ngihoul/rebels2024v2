<?php

namespace App\Repository;

use App\Entity\EventAttendee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventAttendee>
 *
 * @method EventAttendee|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventAttendee|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventAttendee[]    findAll()
 * @method EventAttendee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventAttendeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventAttendee::class);
    }
}
