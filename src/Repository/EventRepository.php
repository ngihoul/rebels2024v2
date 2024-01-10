<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllFuturePaginated(int $page = 1, int $perPage = 10)
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('e')
            ->where('e.date > :now')
            ->setParameter('now', $now)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getResult();
    }

    public function findPendingEventsForThisUser(UserInterface $user)
    {
        $currentDateTime = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->leftJoin('e.attendees', 'a')
            ->where('e.date >= :currentDate')
            ->andWhere('a.user = :user')
            ->andWhere('a.user_response IS NULL')
            ->setParameter('currentDate', $currentDateTime)
            ->setParameter('user', $user)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
