<?php

namespace App\Controller;


use App\Repository\ProductRepository;
use App\Services\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/mon-panier', name: 'app_cart')]
    public function index(Cart $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getCart(),
            'totalWt' => $cart->getTotalWt()
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add(Request $request, Cart $cart, ProductRepository $productRepository,  $id): Response
    {
        $product = $productRepository->findOneById($id);

        $cart->add($product);

        $this->addFlash(
            'success',
            'Produit correctement ajouté au panier'
        );

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/cart/decrease/{id}', name: 'app_cart_decrease')]
    public function decrease(Cart $cart,  $id): Response
    {


        $cart->decrease($id);

        $this->addFlash(
            'success',
            'Produit correctement supprimé du panier'
        );

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove', name: 'app_cart_remove')]
    public function remove(Cart $cart): Response
    {


        $cart->remove();



        return $this->redirectToRoute('app_home',);
    }
}
