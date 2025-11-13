<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\OrderRepository;

final class CustomerController extends AbstractController
{
    #[Route('/customer', name: 'app_customer')]
    public function index(OrderRepository $orderRepository, Security $security): Response
    {
        $user = $security->getUser();
        $userEmail = $user->getUserIdentifier();

        $orders = $orderRepository->findBy(['userEmail' => $userEmail]);

        $totalSpent = array_reduce($orders, function ($carry, $order) {
            return $carry + $order->getTotal();
        },0);

        return $this->render('customer/index.html.twig', [
            'orders' => $orders,
            'totalSpent' => $totalSpent
        ]);
    }

    #[Route('/edit', name: 'profile_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Your profile has been updated!');

            return $this->redirectToRoute('profile_edit');
        }

        return $this->render('customer/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/my-orders', name: 'customer_orders')]
    public function myOrders(OrderRepository $orderRepo, Security $security)
    {
        $user = $security->getUser();

        if(!$user)
        {
            throw $this->createAccessDeniedException('You must be a logged-in user');
        }

        $orders = $orderRepo->findBy([
            'userEmail' => $user->getEmail()
        ], ['orderDate' => 'DESC']);

        return $this->render('customer/orders.html.twig', [
            'orders' => $orders

        ]);
    }


    #[Route('/track-order/{ref}', name: 'track_order')]
    public function trackOrder(string $ref, OrderRepository $orderRepo):Response
    {
        $order = $orderRepo->findOrderWithHistories($ref);

        if(!$order){
            throw $this->createNotFoundException('Order not found!');

        }
        return $this->render('customer/order_tracking.html.twig', [
            'order' => $order
            ]);
    }
       

    #[Route('/generate-invoice/{ref}', name: 'generate_invoice')]
    public function generateInvoice(string $ref, OrderRepository $orderRepository):Response
    {
        $order = $orderRepository->findOneBy(['orderReference' => $ref]);

        if(!$order){
            $this->addFlash('error', 'Order not found!');
            return $this->redirectToRoute('order_history');
            
        }
        return $this->render('customer/invoice.html.twig', [
            'order' => $order
            ]);
       
        
    }
}


