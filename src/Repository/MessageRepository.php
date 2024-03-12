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

    public function getShortMessages(User $currentUser = null)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('PARTIAL m.{id, title, sender, created_at}, s.status, SUBSTRING(m.content, 1, 200) as short_content')
            ->leftJoin('m.messageStatuses', 's')
            ->leftJoin('m.sender', 'u')
            ->andWhere('m.is_archived = :is_archived')
            ->setParameter('is_archived', false)
            ->orderBy('m.created_at', 'DESC');

        if ($currentUser instanceof User) {
            $queryBuilder
                ->andWhere('s.receiver = :currentUser')
                ->setParameter('currentUser', $currentUser);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
