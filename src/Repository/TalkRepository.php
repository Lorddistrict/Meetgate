<?php

namespace App\Repository;

use App\Entity\Talk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Talk|null find($id, $lockMode = null, $lockVersion = null)
 * @method Talk|null findOneBy(array $criteria, array $orderBy = null)
 * @method Talk[]    findAll()
 * @method Talk[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TalkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Talk::class);
    }

    public function getTalksByRate(int $event_id)
    {
        return $this->createQueryBuilder('t')
            ->addSelect('AVG(r.stars) as moy')
            ->addSelect('r')
            ->from('App:Event', 'e')
            ->leftJoin('t.rates', 'r')
            ->where('t.event = e.id')
            ->andWhere('t.event = :event_id')
            ->setParameter('event_id', $event_id)
            ->groupBy('t.id')
            ->orderBy('moy', 'DESC')
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getLastFiveTalks()
    {
        return $this->createQueryBuilder('t')
            ->setMaxResults(5)
            ->orderBy('t.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTop10Talks()
    {
        return $this->createQueryBuilder('t')
            ->addSelect('SUM(r.stars) as qqty')
            ->addSelect('r')
            ->leftJoin('t.rates', 'r')
            ->setMaxResults(10)
            ->groupBy('t.id')
            ->orderBy('qqty', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
