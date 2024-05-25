<?php

namespace App\Repository;

use App\Entity\Stage;
use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Time;

/**
 * @extends ServiceEntityRepository<TimeSlot>
 *
 * @method TimeSlot|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeSlot|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeSlot[]    findAll()
 * @method TimeSlot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSlot::class);
    }

    //    /**
    //     * @return TimeSlot[] Returns an array of TimeSlot objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TimeSlot
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findNextTimeSlotsBasedOn5Minutes(string $dateNow, string $dateLater): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.startTime > :dateNow')
            ->andWhere('t.startTime < :dateLater')
            ->setParameter('dateNow', $dateNow)
            ->setParameter('dateLater', $dateLater)
            ->orderBy('t.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findNextTimeSlots(string $dateNow): TimeSlot
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.startTime > :dateNow')
            ->setParameter('dateNow', $dateNow)
            ->orderBy('t.startTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
