<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart_view')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getDetailedItems(),
            'total' => $cartService->getTotal(),
            'count' => $cartService->getCount(),
        ]);
        
    }

   
    
    #[Route('/cart/add/{id}', name: 'cart_view')]
    public function add(int $id, CartService $cartService): JsonResponse
    {
        $cartService->add($id);
        return new JsonResponse(
            [
              'success' => true,
              'count'  => $cartService->getCount()
            ]
            );
    }
}
