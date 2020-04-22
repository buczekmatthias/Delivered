<?php

namespace App\Repository;

use App\Entity\ChatFiles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ChatFiles|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatFiles|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatFiles[]    findAll()
 * @method ChatFiles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatFilesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatFiles::class);
    }

    // /**
    //  * @return ChatFiles[] Returns an array of ChatFiles objects
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
    public function findOneBySomeField($value): ?ChatFiles
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
