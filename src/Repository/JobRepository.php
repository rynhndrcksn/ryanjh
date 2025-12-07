<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Job;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    /**
     * @return Job[]
     */
    public function findAllOrderedByEndDate(): array
    {
        return $this->createQueryBuilder('j')
            ->orderBy('j.endDate', 'DESC')
            ->addOrderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
