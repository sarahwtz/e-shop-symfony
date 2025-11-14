<?php

namespace App\Controller;

use App\Repository\CartHistoryRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepo, OrderRepository $orderRepo, CartHistoryRepository $cartRepo): Response
    {
        $totalUsers = $userRepo->count([]);
        $totalOrders = $orderRepo->count([]);

        $totalRevenue = $orderRepo->createQueryBuilder('o')
            ->select('SUM(o.total)')
            ->getQuery()
            ->getSingleScalarResult();

        $cartHistories = $cartRepo->findAll();
        $productStats = [];

        foreach ($cartHistories as $history){
            $name = $history->getProductName();
            $quantity = $history->getQuantity();

            if(!isset($productStats[$name])){
                $productStats[$name] = 0;
            }

            $productStats[$name] += $quantity;
        }
        $productLabels = array_keys($productStats);
        $productData = array_values($productStats);

        $orders = $orderRepo->createQueryBuilder('o')
            ->select('SUBSTRING(o.orderDate, 1, 7) AS month, COUNT(o.id) AS total')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();

            $monthlyLabels = [];
            $monthlyValues = [];

            foreach ($orders as $row)
            {
                $monthlyLabels[] = $row['month'];
                $monthlyValues[] = $row[ 'total'];
                
            }

        return $this->render('admin/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalOrders' =>  $totalOrders,
            'totalRevenue' => $totalRevenue,
            'productLabels' => $productLabels,
            'productData' => $productData,
            'monthlyLabels' => $monthlyLabels,
            'monthlyValues' => $monthlyValues


        ]);
    }


    #[Route('/admin/orders', name: 'app_admin_orders')]
    public function listOrders(OrderRepository $orderRepo): Response
    {
        $orders = $orderRepo->findAll();

        return $this->render('admin/order/orders.html.twig',[
            'orders' => $orders,

        ]);
    }
}
