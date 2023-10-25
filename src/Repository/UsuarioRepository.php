<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Utils\Functions;

/**
 * @extends ServiceEntityRepository<Usuario>
 *
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    public function findUsuariosMayoresDe35(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.edad > 35')
            ->getQuery()
            ->getResult();
    }

    public function findNombreConA(): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere($qb->expr()->like('u.nombre', ':patron')) -> setParameter('patron', 'A%');
        $resultado = $qb->getQuery()->getResult();
        return $resultado;
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
    //     * @return Usuario[] Returns an array of Usuario objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Usuario
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
