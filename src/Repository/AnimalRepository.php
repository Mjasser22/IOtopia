<?php

namespace App\Repository;

use App\Entity\Animal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animal>
 */
class AnimalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }
    public function findAllWithDetails()
    {
        return $this->createQueryBuilder('a')
            ->addSelect('s')
            ->leftJoin('a.soins', 's')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function getSpeciesPercentages(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.species, COUNT(a.id) as count')
            ->groupBy('a.species');
        $results = $qb->getQuery()->getResult();

        $total = array_sum(array_column($results, 'count'));
        $percentages = [];
        foreach ($results as $row) {
            $percentages[$row['species']] = $total > 0 ? round(($row['count'] / $total) * 100, 2) : 0;
        }
        return $percentages;
    }
    //    /**
    //     * @return Animal[] Returns an array of Animal objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Animal
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
