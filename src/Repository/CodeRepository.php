<?php

namespace App\Repository;

use App\Entity\Code;
use Cassandra\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Code|null find($id, $lockMode = null, $lockVersion = null)
 * @method Code|null findOneBy(array $criteria, array $orderBy = null)
 * @method Code[]    findAll()
 * @method Code[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Code::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Code $entity, bool $flush = true): void
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
    public function remove(Code $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function countCode(string $code): int {
        return $this->createQueryBuilder('code')
            ->select('COUNT(code.id)')
            ->andWhere('code.Code = :code')
            ->setParameter('code', $code)
            ->getQuery()->getSingleScalarResult();
    }

    public function SelectOrCreate(\DateTime $DateDebut, \DateTime $DateFin ): ?Code {

        // a faire envoie mail pour rajouter le code, au programme du portail

        $code = $this->createQueryBuilder('code')
            ->andWhere('code.DateDebut <= :datedebut')
            ->andWhere('code.DateFin >= :datefin')
            ->setParameter('datedebut', $DateDebut->format('Y-m-d'))
            ->setParameter('datefin', $DateFin->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
        ;

        if(!$code){
            $code = new Code();
            $code->setCode(rand(1000,9999));
            $DateDebut = $DateDebut->modify('first day of this month');
            $DateFin = $DateFin->modify('last day of this month');
            $code->setDateDebut($DateDebut);
            $code->setDateFin($DateFin);
            $this->add($code);
        }

        return $code;
    }

    // /**
    //  * @return Code[] Returns an array of Code objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Code
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
