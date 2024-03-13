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

    public function getShortMessages(User $currentUser, $isAdmin = false)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('PARTIAL m.{id, title, sender, created_at, is_archived}, s.status, SUBSTRING(m.content, 1, 200) as short_content');

        if ($isAdmin) {
            $queryBuilder->leftJoin('m.messageStatuses', 's', 'WITH', 's.receiver = :receiverId');
        } else {
            $queryBuilder
                ->innerJoin('m.messageStatuses', 's', 'WITH', 's.receiver = :receiverId')
                ->andWhere('m.is_archived = :is_archived')
                ->setParameter('is_archived', false);
        }

        $queryBuilder
            ->orderBy('m.created_at', 'DESC')
            ->setParameter('receiverId', $currentUser);

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
}
