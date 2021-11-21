<?php

namespace App\Repository;

use App\Entity\CheckReques;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CheckReques|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckReques|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckReques[]    findAll()
 * @method CheckReques[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckRequesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CheckReques::class);
    }

    // /**
    //  * @return CheckReques[] Returns an array of CheckReques objects
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
    public function findOneBySomeField($value): ?CheckReques
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
