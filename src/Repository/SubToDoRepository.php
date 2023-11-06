<?php

namespace App\Repository;

use App\Entity\SubToDo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubToDo>
 *
 * @method SubToDo|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubToDo|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubToDo[]    findAll()
 * @method SubToDo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubToDoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubToDo::class);
    }

//    /**
//     * @return SubToDo[] Returns an array of SubToDo objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }
}
