<?php

namespace App\Controller;

use App\Service\CartService;
use App\Repository\OrderRepository;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface; 
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

    #[Route('/cart/checkout', name: 'cart_checkout', methods: ['GET','POST'])]
    public function checkout(
        CartService $cartService,
        SessionInterface $session,
        OrderRepository $orderRepository,
        Request $request
    ): Response {
        $items = $cartService->getDetailedItems();
        $total = $cartService->getTotal();

        if (empty($items)) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('cart_view');
        }

        if ($request->isMethod('POST')) {
            $paymentMethod = $request->request->get('payment_method');

            if ($paymentMethod === 'stripe') {
                return $this->redirectToRoute('stripe_checkout');
            }

            if ($paymentMethod === 'paypal') {
                return $this->redirectToRoute('paypal_checkout');
            }
        }

        return $this->render('cart/checkout.html.twig', [
            'items' => $items,
            'total' => $total
        ]);
    }

    #[Route('/cart/stripe/checkout', name: 'stripe_checkout')]
public function stripeCheckout(CartService $cartService, Request $request): Response
{
    $cartItems = $cartService->getDetailedItems();
    if (empty($cartItems)) {
        $this->addFlash('error', 'Your Cart is empty.');
        return $this->redirectToRoute('cart_view');
    }

    $lineItems = [];

    foreach ($cartItems as $item) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['product']->getName(),
                ],
                'unit_amount' => intval($item['product']->getPrice() * 100)
            ],
            'quantity' => $item['quantity'],
        ];
    }

    try {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this
        ]);
    } catch(\Throwable $th){

    }
}

}
