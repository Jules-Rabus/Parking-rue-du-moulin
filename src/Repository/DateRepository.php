<?php

namespace App\Repository;

use App\Entity\Date;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Date|null find($id, $lockMode = null, $lockVersion = null)
 * @method Date|null findOneBy(array $criteria, array $orderBy = null)
 * @method Date[]    findAll()
 * @method Date[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Date::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Date $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }

    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Date $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function selectIfExists( \DateTime $date): ?Date {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->andWhere('d.Date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function selectReservation( \DateTime $date) : ?array {
        return $this->createQueryBuilder('date')
            ->select('reservation')
            ->join('date.id','reservation')
            ->andWhere('date.Date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getResult();

    }
    

    // /**
    //  * @return Date[] Returns an array of Date objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Date
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
