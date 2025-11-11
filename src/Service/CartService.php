<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private SessionInterface $session;
    private ProductRepository $productRepo;

    public function __construct(SessionInterface $session, ProductRepository $productRepo)
    {
        $this->session = $session;
        $this->productRepo = $productRepo;
    }

    public function add(int $id): void
    {
        $cart = $this->session->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $this->session->set('cart', $cart);
    }

    public function getDetailedItems(): array
    {
        $items = $this->session->get('cart', []);
        $detailedItems = [];

        foreach ($items as $productId => $quantity) {
            $product = $this->productRepo->find($productId);
            if (!$product) {
                continue;
            }

            $subtotal = $product->getPrice() * $quantity;
            $detailedItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        return $detailedItems;
    }

    public function getTotal(): float
    {
        return array_sum(array_column($this->getDetailedItems(), 'subtotal'));
    }

    public function getCount(): int
    {
        return array_sum($this->session->get('cart', []));
    }

   public function update(int $id, int $quantity): void
    {
        $cart = $this->session->get('cart', []);

        if ($quantity <= 0) {
            unset($cart[$id]); 
        } else {
            $cart[$id] = $quantity;
        }

        $this->session->set('cart', $cart);
    }


    public function remove(int $id): void
    {
        $cart = $this->session->get('cart', []);
        unset($cart[$id]);
        $this->session->set('cart', $cart);
    }
}
