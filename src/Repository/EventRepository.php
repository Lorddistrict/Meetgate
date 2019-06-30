<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Query
     */
    public function findHomeEventsQuery(): Query
    {
        return $this->createQueryBuilder('e')
            ->getQuery();
    }

    public function getLastFiveEvents()
    {
        return $this->createQueryBuilder('e')
            ->setMaxResults(5)
            ->orderBy('e.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTop10Events()
    {
        return $this->createQueryBuilder('e')
            ->setMaxResults(10)
            ->orderBy('e.price', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
