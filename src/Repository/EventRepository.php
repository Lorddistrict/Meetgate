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

    public function getEventsThisWeek(string $mondayThisWeek, string $sundayThisWeek): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT COUNT(e.id)
            FROM event as e
            WHERE e.created > '01-07-2019 00:00:00'
            AND e.created < '07-07-2019 23:59:59'
            GROUP BY YEAR(e.created)
            ORDER BY
                CONVERT(e.created, DATETIME);
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('mondayThisWeek', $mondayThisWeek);
        $stmt->bindValue('sundayThisWeek', $sundayThisWeek);
        $stmt->execute();
        return $stmt->fetch();

//        return $this->createQueryBuilder('e')
//            ->where('e.created > :mondayThisWeek')
//            ->setParameter('mondayThisWeek', $mondayThisWeek)
//            ->andWhere('e.created < :sundayThisWeek')
//            ->setParameter('sundayThisWeek', $sundayThisWeek)
//            ->
//            ->getQuery()
//            ->getResult();
    }

    /**
     * @param string $firstDayThisMonth
     * @param string $lastDayThisWeek
     * @return array
     */
    public function getEventsThisMonth(string $firstDayThisMonth, string $lastDayThisWeek): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.created > :firstDayThisMonth')
            ->setParameter('firstDayThisMonth', $firstDayThisMonth)
            ->andWhere('e.created < :lastDayThisWeek')
            ->setParameter('lastDayThisWeek', $lastDayThisWeek)
            ->getQuery()
            ->getResult();
    }

}
