<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    private $currentYear;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
        $this->currentYear = (new DateTime())->format('Y');
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAllWithCurrentYearLicense($query, $orderBy, $orderDirection)
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('u', 'l', 'ls')
            ->leftJoin('u.licenses', 'l')
            ->leftJoin('l.subCategories', 'ls')
            ->andWhere('l.season = :currentYear OR l.id IS NULL')
            ->andWhere('u.lastname LIKE :query OR u.firstname LIKE :query')
            ->setParameter('currentYear', $this->currentYear)
            ->setParameter('query', '%' . $query . '%')
            ->addOrderBy($orderBy, $orderDirection);

        return $queryBuilder->getQuery()->getResult();
    }

    public function advancedSearch($searchOptions, $orderBy, $orderDirection)
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('u', 'l', 'ls')
            ->leftJoin('u.licenses', 'l')
            ->leftJoin('l.subCategories', 'ls')
            ->andWhere('l.season = :currentYear OR l.id IS NULL')
            ->setParameter('currentYear', $this->currentYear);

        if (!empty($searchOptions['firstname'])) {
            $queryBuilder
                ->andWhere('u.firstname LIKE :firstname')
                ->setParameter('firstname', '%' . $searchOptions['firstname'] . '%');
        }

        if (!empty($searchOptions['lastname'])) {
            $queryBuilder
                ->andWhere('u.lastname LIKE :lastname')
                ->setParameter('lastname', '%' . $searchOptions['lastname'] . '%');
        }

        if (!empty($searchOptions['gender'])) {
            $queryBuilder
                ->andWhere('u.gender = :gender')
                ->setParameter('gender', $searchOptions['gender']);
        }

        if (!empty($searchOptions['ageMin'])) {
            $dateMin = (new \DateTime())->modify('-' . $searchOptions['ageMin'] . ' years');
            $queryBuilder
                ->andWhere('u.date_of_birth <= :dateMin')
                ->setParameter('dateMin', $dateMin->format('Y-m-d'));
        }

        if (!empty($searchOptions['ageMax'])) {
            $dateMax = (new \DateTime())->modify('-' . $searchOptions['ageMax'] . ' years');
            $queryBuilder
                ->andWhere('u.date_of_birth >= :dateMax')
                ->setParameter('dateMax', $dateMax->format('Y-m-d'));
        }

        if (!empty($searchOptions['licenseStatus'])) {
            $queryBuilder
                ->andWhere('l.status = :licenseStatus')
                ->setParameter('licenseStatus', $searchOptions['licenseStatus']);
        }

        $queryBuilder
            ->addOrderBy($orderBy, $orderDirection);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getUnverified()
    {
        $date = new \DateTime();
        $date->modify('-24 hour');

        return $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere('u.created_at < :date')
            ->andWhere('u.isVerified = 0')
            ->setParameter(':date', $date)
            ->getQuery()
            ->getResult();
    }
}
