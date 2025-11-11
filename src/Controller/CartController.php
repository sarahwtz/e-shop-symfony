<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/cart/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function add(int $id, CartService $cartService): JsonResponse
    {
        $cartService->add($id);
        return new JsonResponse([
            'success' => true,
            'count'  => $cartService->getCount()
        ]);
    }

    #[Route('/cart/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartService $cartService): JsonResponse
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $cartService->update($id, $quantity);

        $detailedItems = $cartService->getDetailedItems();
        $subtotals = [];
        foreach ($detailedItems as $item) {
            $subtotals[$item['product']->getId()] = $item['subtotal'];
        }

        return new JsonResponse([
            'success' => true,
            'count' => $cartService->getCount(),
            'total' => $cartService->getTotal(),
            'subtotals' => $subtotals
        ]);
    }


        #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
        public function remove(int $id, CartService $cartService): JsonResponse
    {
        $cartService->remove($id);

        return new JsonResponse([
            'success' => true,
            'count' => $cartService->getCount(),
            'total' => $cartService->getTotal(),
        ]);
    }

}
