<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @param $countryCode
     * @return array Returns an array of Customer objects
     */

    public function findByCountryCode($countryCode): array
    {
        return $this->createQueryBuilder('customer')
            ->andWhere('customer.phone LIKE :countryCode')
            ->setParameter('countryCode', '('.$countryCode.')%')
            ->getQuery()
            ->getResult()
        ;
    }

}
