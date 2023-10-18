<?php

namespace App\Repository;

use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Utils\Functions;

/**
 * @extends ServiceEntityRepository<Producto>
 *
 * @method Producto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Producto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Producto[]    findAll()
 * @method Producto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producto::class);
    }

    public function findAllWithPagination(int $currentPage, int $limit): Paginator
    {
        // Creamos nuestra query
        $query = $this->createQueryBuilder('p')->getQuery();
        // Creamos un paginator con la funcion paginate
        $paginator = Functions::paginate($query, $currentPage, $limit);
        return $paginator;
    }

//    /**
//     * @return Producto[] Returns an array of Producto objects
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

//    public function findOneBySomeField($value): ?Producto
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
