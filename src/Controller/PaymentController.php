<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Services\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    #[Route('/commande/paiement/{id_order}', name: 'app_payment')]
    public function index(OrderRepository $orderRepository, EntityManagerInterface $entityManager, $id_order): Response
    {
        $YOUR_DOMAIN = $_ENV['DOMAIN'];

        Stripe::setApiKey($_ENV['STRIPE_KEY']);

        $order = $orderRepository->findOneBy([
            'id' => $id_order,
            'user' => $this->getUser(),
        ]);

        if (!$order) {
            return $this->redirectToRoute('app_home');
        }

        $products_for_stripe = [];

        /**
         * @var OrderDetail $product
         */
        foreach ($order->getOrderDetails() as $product) {


            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => number_format($product->getProductPriceWt() * 100, 0, '', ''),
                    'product_data' => [
                        'name' => $product->getProductName(),
                        'images' => [
                            $YOUR_DOMAIN . '/uploads/' . $product->getProductIllustration(),
                        ]
                    ]
                ],
                'quantity' => $product->getProductQuantity(),
            ];
        }

        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => number_format($order->getCarrierPrice() * 100, 0, '', ''),
                'product_data' => [
                    'name' => 'Transporteur : ' . $order->getCarrierName(),
                ]
            ],
            'quantity' => 1,
        ];


        /**
         * @var User $user
         */
        $user = $this->getUser();

        $checkout_session = Session::create([
            'customer_email' => $user->getEmail(),
            'line_items' => [[
                $products_for_stripe,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/mon-panier/annulation',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $entityManager->flush();

        return $this->redirect($checkout_session->url);
    }

    #[Route('/commande/merci/{stripe_session_id}', name: 'app_payment_success')]
    public function success(OrderRepository $orderRepository, EntityManagerInterface $entityManager, Cart $cart, $stripe_session_id): Response
    {
        $order = $orderRepository->findOneBy([
            'stripe_session_id' => $stripe_session_id,
            'user' => $this->getUser(),
        ]);

        if (!$order) {
            return $this->redirectToRoute('app_home');
        }

        if ($order->getState() == 1) {
            $order->setState(2);
            $cart->remove();
            $entityManager->flush();
        }

        return $this->render('payment/success.html.twig', [

            'order' => $order,

        ]);
    }
}
