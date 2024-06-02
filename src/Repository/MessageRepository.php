<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    // get short message to display in homepage summary and messages list
    public function getShortMessages(User $currentUser, $isAdmin = false)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('PARTIAL m.{id, title, content, sender, created_at, is_archived}, s.status')
            ->leftJoin('m.messageStatuses', 's');

        if (!$isAdmin) {
            $queryBuilder
                // Get message where sender is current user for coaches
                ->andWhere('(m.is_archived = :is_archived AND s.receiver = :receiverId) OR m.sender = :currentUser')
                ->setParameter('is_archived', false)
                ->setParameter('receiverId', $currentUser)
                ->setParameter('currentUser', $currentUser);
        }

        $queryBuilder->orderBy('m.created_at', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function countUnreadMessagesForThisUser(User $user)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.messageStatuses', 's')
            ->select('COUNT(s.id)')
            ->andWhere('s.status = :unread')
            ->andWhere('s.receiver = :user')
            ->andWhere('m.is_archived = :is_archived')
            ->setParameters(['unread' => false, 'user' => $user, 'is_archived' => false])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findThreeLatest(User $user)
    {
        return $this->createQueryBuilder('m')
            ->select('PARTIAL m.{id, title, content, sender, created_at, is_archived}, s.status')
            ->innerJoin('m.messageStatuses', 's', 'WITH', 's.receiver = :receiver')
            ->andWhere('m.is_archived = :is_archived')
            ->setParameter('receiver', $user)
            ->setParameter('is_archived', false)
            ->orderBy('m.created_at', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
}
