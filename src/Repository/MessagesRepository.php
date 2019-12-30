<?php

namespace App\Repository;

use App\Entity\Messages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @method Messages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Messages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Messages[]    findAll()
 * @method Messages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, SessionInterface $session)
    {
        parent::__construct($registry, Messages::class);
        $this->session = $session;
    }

    public function getConversation($id)
    {
        $currentUserMessages = $this->createQueryBuilder('m')
            ->andWhere('m.Source = :logged OR m.Target = :logged')
            ->setParameter('logged', $this->session->get('user')->getId())
            ->orderBy('m.Date', 'DESC')
            ->getQuery()
            ->getResult();

        $result = array();

        foreach ($currentUserMessages as $k => $v) {
            if ($v->getSource()->getId() == $id || $v->getTarget()->getId() == $id) {
                $result[] = $v;
            }
        }

        return $result;
    }

    // /**
    //  * @return Messages[] Returns an array of Messages objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Messages
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
