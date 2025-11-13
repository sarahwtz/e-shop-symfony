<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepo, OrderRepository $orderRepo): Response
    {
        $totalUsers = $userRepo->count([]);
        $totalOrders = $orderRepo->count([]);

        $totalRevenue = $orderRepo->createQueryBuilder('o')
            ->select('SUM(o.total)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('admin/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalOrders' =>  $totalOrders,
            'totalRevenue' => $totalRevenue,

        ]);
    }
}
