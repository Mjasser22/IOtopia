<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    // src/Repository/ProduitRepository.php
    public function findByCategoryAndSearchQuery(int $categoryId, string $query, ?string $sortBy = ''): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.categorie = :categoryId')
            ->andWhere('p.nom LIKE :query OR p.description LIKE :query')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('query', '%' . $query . '%');

        // Apply sorting
        if ($sortBy === 'price-asc') {
            $queryBuilder->orderBy('p.prix', 'ASC');
        } elseif ($sortBy === 'price-desc') {
            $queryBuilder->orderBy('p.prix', 'DESC');
        }

        return $queryBuilder->getQuery()->getResult();
    }
    public function findByCategoryAndSort(int $categoryId, ?string $sortBy = ''): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.categorie = :categoryId')
            ->setParameter('categoryId', $categoryId);

        // Apply sorting
        if ($sortBy === 'price-asc') {
            $queryBuilder->orderBy('p.prix', 'ASC');
        } elseif ($sortBy === 'price-desc') {
            $queryBuilder->orderBy('p.prix', 'DESC');
        }

        return $queryBuilder->getQuery()->getResult();
    }
    public function findBySearchQuery(string $query, ?string $sortBy = ''): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.nom LIKE :query OR p.description LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        // Apply sorting
        if ($sortBy === 'price-asc') {
            $queryBuilder->orderBy('p.prix', 'ASC');
        } elseif ($sortBy === 'price-desc') {
            $queryBuilder->orderBy('p.prix', 'DESC');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
