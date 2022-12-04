<?php

namespace App\Repository;

use App\Entity\Code;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\MailerInterface;

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

    public function SelectOrCreate(\DateTime $dateDebut, \DateTime $dateFin, MailerInterface $mailer, $flush = true ): ?Code {

        $dateDebut = clone $dateDebut;
        $dateFin = clone $dateFin;

        $dateDebut = $dateDebut->modify('first day of this month');
        $dateFin = $dateFin->modify('last day of this month');

        $code = $this->createQueryBuilder('code')
            ->andWhere('code.DateDebut = :datedebut')
            ->andWhere('code.DateFin = :datefin')
            ->setParameter('datedebut', $dateDebut->format('Y-m-d'))
            ->setParameter('datefin', $dateFin->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
        ;

        if($code) return $code;

            $code = new Code();
            $code->setCode(rand(1000,9999));
            $code->setDateDebut($dateDebut);
            $code->setDateFin($dateFin);
            $this->add($code,$flush);

            // Correction problème insertion bdd
            sleep(1);

            $email = new Email();
            $email->from(new Address('gestion@parking-rue-du-moulin.fr','Gestion Parking'))
                ->to('jules200204@gmail.com')
                ->subject('Nouveau code '. $code->getCode())
                ->text("Nouveau code à ajouter : " . $code->getCode());
            //$mailer->send($email);

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
