<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use FOS\RestBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProductController extends AbstractController
{
    use ControllerTrait;

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
     * @Rest\Get(
     *     path = "/products/{id}",
     *     name = "show_product",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 200)
     */
    public function showProduct(Product $product)
    {
        return $product;
    }

    /**
     * @Rest\Post("/products", name="create_product")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("product", converter="fos_rest.request_body")
     */
    public function createProduct(Product $product)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->persist($product);
        $manager->flush();

        return $this->view($product, Response::HTTP_CREATED, ['Location' => $this->generateUrl('show_product', ['id' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Rest\Put("/products", name="edit_product")
     * @Rest\View(StatusCode = 200)
     * @ParamConverter("product", converter="fos_rest.request_body")
     */
    public function editProduct(Product $product)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->persist($product);
        $manager->flush();

        return $this->view($product, Response::HTTP_OK, ['Location' => $this->generateUrl('show_product', ['id' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Rest\Delete(
     *  path = "/products/{id}",
     *  name = "delete_product",
     *  requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 204)
     * @ParamConverter("product", converter="fos_rest.request_body")
     */
    public function deleteProduct(Product $product)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($product);
        $manager->flush();

        return $this->view('', Response::HTTP_NO_CONTENT);
    }
}
