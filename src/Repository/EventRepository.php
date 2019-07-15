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

    /**
     * @param string $mondayThisWeek
     * @param string $sundayThisWeek
     * @return array
     */
    public function getEventsThisWeek(string $mondayThisWeek, string $sundayThisWeek): array
    {
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');

        return $this->createQueryBuilder('e')
            ->where('e.created > :mondayThisWeek')
            ->setParameter('mondayThisWeek', $mondayThisWeek)
            ->andWhere('e.created < :sundayThisWeek')
            ->setParameter('sundayThisWeek', $sundayThisWeek)
            ->addSelect('DAY(e.created) AS day, COUNT(DAY(e.created)) AS num')
            ->groupBy('day')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $firstDayThisMonth
     * @param string $lastDayThisMonth
     * @return array
     */
    public function getEventsThisMonth(string $firstDayThisMonth, string $lastDayThisMonth): array
    {
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('WEEK', 'DoctrineExtensions\Query\Mysql\Week');

        return $this->createQueryBuilder('e')
            ->where('e.created > :firstDayThisMonth')
            ->setParameter('firstDayThisMonth', $firstDayThisMonth)
            ->andWhere('e.created < :lastDayThisMonth')
            ->setParameter('lastDayThisMonth', $lastDayThisMonth)
            ->addSelect('WEEK(e.created) AS week, COUNT(WEEK(e.created)) AS num')
            ->groupBy('week')
            ->getQuery()
            ->getResult();
    }

}
