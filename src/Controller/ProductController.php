<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/products", name="list_product", methods={"GET"})
     */
    public function listProducts(ProductRepository $productRepository, SerializerInterface $serialize)
    {
        $products = $productRepository->findAll();

        $data = $serialize->serialize($products, 'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/products/{id}", name="show_product")
     */
    public function showProduct(SerializerInterface $serialize)
    {
        $product = new Product();
        $product
            ->setName('article')
            ->setDescription('Le contenu de mon article.');
        $data = $serialize->serialize($product, 'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
