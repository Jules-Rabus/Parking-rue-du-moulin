<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Reservation $entity, bool $flush = true): void
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
    public function remove(Reservation $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function NombreReservationClient(int $id){

        return $this->createQueryBuilder('reservation')
            ->select('COUNT(reservation.id)')
            ->andWhere('reservation.Client = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function NombreReservationTelephone(string $Telephone){
        return $this->createQueryBuilder('reservation')
            ->select('COUNT(reservation.id)')
            ->andWhere('reservation.Telephone = :telephone')
            ->setParameter('telephone', $Telephone)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function ReservationStatistiqueMois( \DateTime $date){
        return $this->createQueryBuilder('reservation')
            ->select('reservation.DateArrivee, reservation.DateDepart, reservation.NombrePlace')
            ->where('reservation.DateArrivee LIKE :date')
            ->setParameter('date', $date->format('Y-m') . '%')
            ->getQuery()
            ->getResult();
    }

    public function ReservationStatistiqueAnnee( \DateTime $date){
        return $this->createQueryBuilder('reservation')
            ->select('reservation.DateArrivee, reservation.DateDepart, reservation.NombrePlace')
            ->where('reservation.DateArrivee LIKE :date')
            ->setParameter('date', $date->format('Y') . '%')
            ->getQuery()
            ->getResult();
    }

    public function ReservationStatistiqueNombre(){
        return $this->createQueryBuilder('reservation')
            ->select('count(reservation.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function ReservationStatistiqueNombreClientMax(){

        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT client.id as client, client.count AS max FROM (SELECT COUNT(reservation.id) AS count, reservation.client_id AS id FROM reservation GROUP BY reservation.client_id) AS client ORDER BY client.count DESC LIMIT 1';
        $query = $conn->prepare($sql);
        $query = $query->executeQuery();

        return $query->fetchAssociative();

    }


    

    // /**
    //  * @return Reservation[] Returns an array of Reservation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
