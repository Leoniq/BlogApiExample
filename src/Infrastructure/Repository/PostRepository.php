<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    /**
     * PostRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @param Post $entity
     */
    public function save(Post $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Post $post
     */
    public function update(Post $post): void
    {
        $this->getEntityManager()->merge($post);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Post $entity
     */
    public function remove(Post $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @return Post
     */
    public function findLastInsert(): Post
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        return current($query);
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return array|null
     */
    public function findAllPosts(int $page, int $limit, array $filters = []): ?array
    {
        $query = $this->createQueryBuilder('p');

        $sortBy = null !== $filters['sortBy'] ? 'p.' . $filters['sortBy'] : 'p.createdAt';
        $orderBy = null !== $filters['orderBy'] ? $filters['orderBy'] : 'ASC';

        $query->orderBy($sortBy, $orderBy);
        $query->setFirstResult(($page - 1) * $limit);
        $query->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findTotal(): int
    {
        $query = $this->createQueryBuilder('p');
        $query->select('count(p.id)');

        return $query->getQuery()->getSingleScalarResult();
    }
}
