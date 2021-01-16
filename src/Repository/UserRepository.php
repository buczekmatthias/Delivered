<?php

namespace App\Repository;

use App\Entity\User;
use App\Services\UserServices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private $userServices;

    public function __construct(ManagerRegistry $registry, UserServices $userServices)
    {
        parent::__construct($registry, User::class);
        $this->userServices = $userServices;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getUnfriendedUsers(object $current)
    {
        $all = $this->createQueryBuilder('u')
            ->andWhere('u.login != :current')
            ->setParameter(":current", $current->getLogin())
            ->getQuery()
            ->getResult();

        $freeToAdd = [];

        $friendsIds = $this->userServices->getFriendsIds($current);

        $invitationsIds = $this->userServices->getUsersFromInvitations($current, "ids");

        foreach ($all as $user) {
            if (!in_array($user->getId(), $friendsIds) && !in_array($user->getId(), $invitationsIds)) {
                $freeToAdd[] = $user;
            }
        }

        return $freeToAdd;
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
