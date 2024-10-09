<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Form\OrderType;
use App\Services\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/commande/livraison', name: 'app_order')]
    public function index(): Response
    {

        /**
         * @var User $user
         */
        $user = $this->getUser();
        $addresses = $user->getAddresses();

        if (count($addresses) == 0) {
            return $this->redirectToRoute('app_account_address_form');
        }
        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $addresses,
            'action' => $this->generateUrl('app_order_summary'),
        ]);
        return $this->render('order/index.html.twig', [
            'deliverForm' => $form->createView(),
        ]);
    }

    #[Route('/commande/recapitulatif', name: 'app_order_summary')]
    public function add(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        if ($request->getMethod() !== 'POST') {
            return $this->redirectToRoute('app_cart');
        }

        $products = $cart->getCart();



        /**
         * @var User $user
         */
        $user = $this->getUser();
        $addresses = $user->getAddresses();
        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $addresses,

        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $addressObject = $form->get('addresses')->getData();
            $address = $addressObject->getFirstname() . ' ' . $addressObject->getLastname() . '<br/>';
            $address .= $addressObject->getAddress() . '<br/>';
            $address .= $addressObject->getPostal() . ' ' . $addressObject->getCity() . '<br/>';
            $address .= $addressObject->getCountry() . '<br/>';
            $address .= $addressObject->getPhone();

            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt(new \DateTime());
            $order->setState(1);
            $order->setCarrierName($form->get('carriers')->getData()->getName());
            $order->setCarrierPrice($form->get('carriers')->getData()->getPrice());
            $order->setDelivery($address);

            foreach ($products as $product) {
                $orderDetail = new OrderDetail();
                $orderDetail->setProductName($product['object']->getName());
                $orderDetail->setProductIllustration($product['object']->getIllustration());
                $orderDetail->setProductPrice($product['object']->getPrice());
                $orderDetail->setProductTva($product['object']->getTva());
                $orderDetail->setProductQuantity($product['quantity']);

                $order->addOrderDetail($orderDetail);
            }



            $entityManager->persist($order);
            $entityManager->flush();
            // $this->addFlash(
            //     'success',
            //     'Votre compte a bien été crée. Veuillez vous connecter'
            // );

            // return $this->redirectToRoute('app_login');
        }

        return $this->render('order/summary.html.twig', [
            'choices' => $form->getData(),
            'cart' => $products,
            'totalWt' => $cart->getTotalWt(),
        ]);
    }
}
