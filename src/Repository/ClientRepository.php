<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Client $entity, bool $flush = true): void
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
    public function remove(Client $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Client) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function countEmail(string $contact): int {
        return $this->createQueryBuilder('client')
            ->select('COUNT(client.id)')
            ->where('client.email = :contact')
            ->setParameter('contact', $contact)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTelephone(string $contact): int {
        return $this->createQueryBuilder('client')
            ->select('COUNT(client.id)')
            ->where('client.telephone = :contact')
            ->setParameter('contact', $contact)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Recherche des clients en fonction du telephone
    public function rechercheContactTelephone(string $contact) : array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT C.id, C.nom, C.telephone, count(R.id) as nombreReservation FROM client C LEFT JOIN reservation R ON C.id = R.client_id WHERE C.telephone LIKE :telephone GROUP BY C.id ORDER BY count(R.id) DESC LIMIT 5" ;
        $query = $conn->prepare($sql);
        $query->BindValue(':telephone', '%' . $contact . '%');
        $query = $query->executeQuery();

        return $query->fetchAllAssociative();
    }

    // Recherche des clients en fonction du nom
    public function rechercheContactNom(string $contact) : array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT C.id, C.nom, C.telephone, C.email , count(R.id) as nombreReservation FROM client C LEFT JOIN reservation R ON C.id = R.client_id WHERE C.nom LIKE :nom GROUP BY C.id ORDER BY count(R.id) DESC LIMIT 5" ;
        $query = $conn->prepare($sql);
        $query->BindValue(':nom', '%' . $contact . '%');
        $query = $query->executeQuery();

        return $query->fetchAllAssociative();
    }

    // Recherche des clients en fonction du mail
    public function rechercheContactMail(string $contact) : array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT C.id, C.nom, C.email, count(R.id) as nombreReservation FROM client C LEFT JOIN reservation R ON C.id = R.client_id WHERE C.email LIKE :email GROUP BY C.id ORDER BY count(R.id) DESC LIMIT 5" ;
        $query = $conn->prepare($sql);
        $query->BindValue(':email', '%' . $contact . '%');
        $query = $query->executeQuery();

        return $query->fetchAllAssociative();
    }

    // /**
    //  * @return Client[] Returns an array of Client objects
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
    public function findOneBySomeField($value): ?Client
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
