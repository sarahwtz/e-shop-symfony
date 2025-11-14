<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CartHistoryRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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


    #[Route('/admin/orders/update', name: 'app_admin_orders_update')]
    public function uploadOrdersStatus(
        Request $request,
        OrderRepository $orderRepo,
        EntityManagerInterface $em
    ): Response
    {
        $statuses = $request->request->all('statuses');

        foreach($statuses as $orderId => $status){
            $order = $orderRepo->find($orderId);
            if(!$order) continue;

            $order->setIsPending(isset($status['pending']));
            $order->setIsProcessed(isset($status['processed']));
            $order->setIsShipped(isset($status['shipped']));
            $order->setIsDelivered(isset($status['delivered']));
        }

        $em->flush();

        $this->addFlash('success', 'Order updated successfully!');

        return $this->redirectToRoute('app_admin_orders');
    }


    #[Route('/admin/orders/{ref}', name: 'app_admin_orders_details')]
    public function viewOrder(string $ref, OrderRepository $orderRepo):Response
    {
        $order = $orderRepo->findOneBy(['orderReference' => $ref]);

        if(!$order){
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('admin/order/order_details.html.twig', [
            'order' => $order
        ]);
    }


    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(EntityManagerInterface $em)
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users
        ]);
    }
}
