<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    public function __construct(private RequestStack $requestStack) {}

    public function add($product)
    {


        $cart = $this->getCart();

        $productId = $product->getId();

        if (isset($cart[$productId])) {

            $cart[$productId] = [
                'object' => $product,
                'quantity' => $cart[$productId]['quantity'] + 1,
            ];
        } else {
            $cart[$productId] = [
                'object' => $product,
                'quantity' => 1,
            ];
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function decrease($id)
    {


        $cart = $this->getCart();

        if ($cart[$id]['quantity'] > 1) {
            $cart[$id]['quantity'] = $cart[$id]['quantity'] - 1;
        } else {
            unset($cart[$id]);
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function remove()
    {
        return $this->requestStack->getSession()->remove('cart');
    }

    public function getCart()
    {
        return $this->requestStack->getSession()->get('cart');
    }

    public function fullQuantity()
    {
        $cart = $this->getCart();
        $quantity = 0;
        if (!isset($cart)) return 0;
        foreach ($cart as $product) {
            $quantity = $quantity + $product['quantity'];
        }

        return $quantity;
    }

    public function getTotalWt()
    {
        $cart = $this->getCart();
        $price = 0;
        if (!isset($cart)) return 0;
        foreach ($cart as $product) {
            $price = $price + ($product['object']->getPriceWt() * $product['quantity']);
        }

        return $price;
    }
}
