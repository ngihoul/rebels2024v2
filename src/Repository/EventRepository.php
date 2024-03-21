<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\User;
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

    public function findAll()
    {
        $currentDateTime = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->where('e.date >= :currentDate')
            ->orderBy('e.date', 'ASC')
            ->setParameter('currentDate', $currentDateTime)
            ->getQuery()
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
            ->andWhere('e.is_cancelled = :is_cancelled')
            ->setParameter('currentDate', $currentDateTime)
            ->setParameter('user', $user)
            ->setParameter('is_cancelled', false)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFutureEventsForThisUser(UserInterface $user)
    {
        $currentDateTime = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->leftJoin('e.attendees', 'a')
            ->where('e.date >= :currentDate')
            ->andWhere('a.user = :user')
            ->andWhere('a.user_response IS NOT NULL')
            ->setParameter('currentDate', $currentDateTime)
            ->setParameter('user', $user)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findYearsWithAttendeesForUser(User $user, EventCategory $category): array
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('YEAR(e.date) AS year')
            ->leftJoin('e.attendees', 'ea')
            ->leftJoin('e.category', 'ec')
            ->where('ea.user = :user')
            ->andWhere('e.category = :category')
            ->andWhere('e.is_cancelled = :is_cancelled')
            ->setParameter('user', $user)
            ->setParameter('category', $category)
            ->setParameter('is_cancelled', false)
            ->groupBy('year')
            ->orderBy("year", "DESC");

        $results = $queryBuilder->getQuery()->getResult();

        $years = [];
        foreach ($results as $result) {
            $years[] = $result['year'];
        }

        return $years;
    }

    public function countUserResponses(User $user, int $year, EventCategory $category): array
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('COUNT(ea.user_response) AS response_count, ea.user_response')
            ->leftJoin('e.attendees', 'ea')
            ->where('ea.user = :userId')
            ->andWhere('e.category = :category')
            ->andWhere('YEAR(e.date) = :date')
            ->andWhere('e.is_cancelled = :is_cancelled')
            ->setParameter('userId', $user->getId())
            ->setParameter('category', $category)
            ->setParameter('date', $year)
            ->setParameter('is_cancelled', false)
            ->groupBy('ea.user_response');

        $results = $queryBuilder->getQuery()->getResult();

        $formattedResults = [
            'presence' => 0,
            'absence' => 0,
            'no_reply' => 0,
        ];

        foreach ($results as $result) {
            if ($result['user_response'] === true) {
                $formattedResults['presence'] = $result['response_count'];
            } elseif ($result['user_response'] === false) {
                $formattedResults['absence'] = $result['response_count'];
            } else {
                $formattedResults['no_reply'] = $result['response_count'];
            }
        }

        return $formattedResults;
    }

    public function countTotalEvent(User $user, int $year, EventCategory $category)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('COUNT(e.id) AS total_events')
            ->leftJoin('e.attendees', 'ea')
            ->where('ea.user = :userId')
            ->andWhere('e.category = :category')
            ->andWhere('YEAR(e.date) = :date')
            ->andWhere('e.is_cancelled = :is_cancelled')
            ->setParameter('userId', $user->getId())
            ->setParameter('category', $category)
            ->setParameter('date', $year)
            ->setParameter('is_cancelled', false);


        $totalEventsResult = $queryBuilder->getQuery()->getSingleResult();
        $totalEvents = $totalEventsResult['total_events'];

        return $totalEvents;
    }
}
