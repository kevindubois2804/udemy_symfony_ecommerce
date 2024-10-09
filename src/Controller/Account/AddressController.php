<?php

namespace App\Controller\Account;

use App\Entity\Address;
use App\Form\AddressUserType;
use App\Repository\AddressRepository;
use App\Services\Cart;
use App\Snippets\RefererRouteInfoSnippet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AddressController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/compte/adresses', name: 'app_account_addresses')]
    public function index(): Response
    {
        return $this->render('account/address/index.html.twig');
    }

    #[Route('/compte/adresses/delete/{id}', name: 'app_account_address_delete')]
    public function delete(AddressRepository $addressRepository, $id): Response
    {
        $address = $addressRepository->findOneById($id);

        if (!$address || $address->getUser() !== $this->getUser()) {
            $this->addFlash(
                'danger',
                'Vous ne pouvez pas supprimer cette addresse'
            );

            return $this->redirectToRoute('app_account_addresses');
        }

        $this->entityManager->remove($address);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            'Votre addresse est correctement supprimée'
        );


        return $this->redirectToRoute('app_account_addresses');
    }

    #[Route('/compte/adresse/ajouter/{id}', name: 'app_account_address_form', defaults: ['id' => null])]
    public function form(Request $request, AddressRepository $addressRepository, Cart $cart, $id)
    {

        if ($id) {
            $address = $addressRepository->findOneById($id);
            if (!$address || $address->getUser() !== $this->getUser()) {
                $this->addFlash(
                    'danger',
                    'Vous ne pouvez pas modifier cette addresse'
                );

                return $this->redirectToRoute('app_account_addresses');
            }
        } else {
            $address = new Address();
            $address->setuser($this->getUser());
        }


        $form = $this->createForm(AddressUserType::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Votre addresse a bien été ajoutée dans votre liste d\'addresses'
            );

            if ($cart->fullQuantity() > 0) {
                return $this->redirectToRoute('app_order');
            }

            return $this->redirectToRoute('app_account_addresses');
        }
        return $this->render('account/address/form.html.twig', [
            'addressForm' => $form,
        ]);
    }
}
