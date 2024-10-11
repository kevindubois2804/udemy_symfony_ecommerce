<?php

namespace App\Controller\Account;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/compte/commande/{id_order}', name: 'app_account_order')]
    public function index(OrderRepository $orderRepository, $id_order): Response
    {

        $order = $orderRepository->findOneBy([
            'id' => $id_order,
            'user' => $this->getUser(),
        ]);

        if (!$order) {
            return $this->redirect('app_home');
        }

        return $this->render('account/order/index.html.twig', [
            'order' => $order,
        ]);
    }
}
