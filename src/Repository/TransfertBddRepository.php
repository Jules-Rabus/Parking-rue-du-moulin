<?php

namespace App\Repository;

use App\Entity\TransfertBdd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TransfertBdd|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransfertBdd|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransfertBdd[]    findAll()
 * @method TransfertBdd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransfertBddRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransfertBdd::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(TransfertBdd $entity, bool $flush = true): void
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
    public function remove(TransfertBdd $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return TransfertBdd[] Returns an array of TransfertBdd objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TransfertBdd
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
