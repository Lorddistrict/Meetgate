<?php

namespace App\Repository;

use App\Entity\Rate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Rate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rate[]    findAll()
 * @method Rate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Rate::class);
    }

    public function getRatesByEvents($event_id, $talk_id) : array
    {
        return $this->createQueryBuilder('r')
//            Debug
//            ->select('e.id as event_id, t.id as talk_id, COUNT(r.id) as nbRate, SUM(r.stars) as cumulRates')
            ->select('t.id as talk_id, COUNT(r.id) as nbRate, SUM(r.stars) as cumulRates')
            ->from('App:Talk', 't')
            ->from('App:Event', 'e')
            ->where('e.id = t.event')
            ->andWhere('t.id = r.talk')
            ->andWhere('e.id = :event_id')
            ->andWhere('t.id = :talk_id')
            ->setParameter('event_id', $event_id)
            ->setParameter('talk_id', $talk_id)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Rate[] Returns an array of Rate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Rate
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
