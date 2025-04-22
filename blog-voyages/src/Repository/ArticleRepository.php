<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Recherche les articles selon un titre partiel et/ou un auteur donné.
     *
     * @param string|null $title
     * @param User|null   $author
     *
     * @return Article[]
     */
    public function findByFilters(?string $title, ?User $author): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC');

        if ($title) {
            $qb->andWhere('a.titre LIKE :title')
               ->setParameter('title', '%'.$title.'%');
        }

        if ($author) {
            $qb->andWhere('a.auteur = :author')
               ->setParameter('author', $author);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère la liste des auteurs ayant au moins un article.
     *
     * @return User[]
     */
    public function findAuthors(): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.auteur', 'u')          // on joint la relation ManyToOne
            ->select('DISTINCT u')           // on sélectionne les auteurs distincts
            ->orderBy('u.email', 'ASC')      // optionnel : tri par email
            ->getQuery()
            ->getResult();
    }
}
