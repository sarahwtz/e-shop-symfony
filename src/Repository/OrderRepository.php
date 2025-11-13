<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function saveOrder(Order $order): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($order);
        $entityManager->flush();
    }

    public function findOrderWithHistories(string $orderReference): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.cartHistories', 'h')
            ->addSelect('h')
            ->where('o.orderReference = :ref')
            ->setParameter('ref', $orderReference)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
