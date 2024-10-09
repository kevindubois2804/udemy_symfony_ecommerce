<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use App\Services\Cart;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

class AppExtensions extends AbstractExtension implements GlobalsInterface
{
    private $categoryRepository;
    private $cart;
    public function __construct(CategoryRepository $categoryRepository, Cart $cart)
    {
        $this->categoryRepository = $categoryRepository;
        $this->cart = $cart;
    }
    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice'])
        ];
    }

    public function getGlobals(): array
    {
        return [
            'allCategories' => $this->categoryRepository->findAll(),
            'fullCartQuantity' => $this->cart->fullQuantity(),
        ];
    }

    public function formatPrice($number)
    {
        return number_format($number, '2', ',') . ' €';
    }
}
