<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, SessionInterface $session)
    {
        parent::__construct($registry, User::class);
        $this->session = $session->get('user');
    }

    public function registerVerify($data)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.Login = :login OR u.Email = :email')
            ->setParameter('login', $data[0])
            ->setParameter('email', $data[1])
            ->getQuery()
            ->getResult();
    }

    public function findConversations($id)
    {
        $recentUnique = $this->createQueryBuilder('u')
            ->andWhere('m.Source = :id OR m.Target = :id')
            ->setParameter('id', $id)
            ->innerjoin('u.writtenMessages', 'm')
            ->orderBy('m.Date', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();

        $recentConvs = array();

        foreach ($recentUnique as $k => $v) {
            if ($v->getLogin() !== $this->session->getLogin() && !in_array($v, $recentConvs)) {
                $recentConvs[] = $this->findOneBy(['id' => $v]);
            }
        }

        return $recentConvs;
    }

    public function findPotential()
    {
        $amount = $this->findOneBy(array(), array('id' => 'DESC'))->getId();

        $possibilities = array();
        $used = [];
        while (sizeof($possibilities) < 5) {
            $rand = rand(1, $amount);
            if ($rand !== $this->session->getId() && !in_array($rand, $used)) {
                $possibilities[] = $this->findOneBy(['id' => $rand]);
                $used[] = $rand;
            }
        }
        return $possibilities;
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
