<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\BelongsTo;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<BelongsTo>
 *
 * @method BelongsTo|null find($id, $lockMode = null, $lockVersion = null)
 * @method BelongsTo|null findOneBy(array $criteria, array $orderBy = null)
 * @method BelongsTo[]    findAll()
 * @method BelongsTo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BelongsToRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BelongsTo::class);
    }

    public function add(BelongsTo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BelongsTo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   
  /**
    * @return BelongsTO[] Returns an array of BelongsTO objects
    * @param string|null $string to find in 
    */
    public function findTeamsByUser(user $user): ?array
    {   
        $result = $this->createQueryBuilder('b')
            ->select('t.id, t.title, b.validated, b.teamRoles')
            ->innerJoin('b.team', 't')
            ->where('b.user = :user')
            ->setParameter('user',$user)
            ->getQuery()
            ->getResult();

        return $result;
    }

    // public function findTeamsByUser(User $user) :?array
    // {
    //     if(!$user){return null;}

    //     $conn = $this->getEntityManager()->getConnection();

    //     $sql = 'SELECT team.id, team.title, validated, team_roles
    //         FROM belongs_to AS b
    //         INNER JOIN team ON team_id = team.id
    //         WHERE user_id = :id';

    //     $resultSet = $conn->executeQuery($sql, ['id' => $user->getId()]);
    
    //     return $resultSet->fetchAllAssociative();
    // }
    
    //    public function findOneBySomeField($value): ?BelongsTo
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}