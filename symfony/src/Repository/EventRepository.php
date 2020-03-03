<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[] Returns an array of Event objects
     */
    
    public function getFutureEvents()
    {
        $now = new \DateTime('now-8hours');
        return $this->createQueryBuilder('e')
            ->andWhere('e.EventDate > :date')
            ->andWhere('e.type != :type')
            ->setParameter('date', $now)
            ->setParameter('type', 'Announcement')
            ->orderBy('e.EventDate', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findEventsByType($type)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.type = :val')
            ->setParameter('val', $type)
            ->orderBy('r.EventDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findEventsByNotType($type)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.type != :val')
            ->setParameter('val', $type)
            ->orderBy('r.EventDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
