<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\Paginator;
use App\Service\ViolationsChecker;
use App\Repository\ProductRepository;
use FOS\RestBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class ProductController extends AbstractController
{
    use ControllerTrait;
    use ViolationsChecker;

    /**
     * @Rest\Get("/products", name="list_products")
     * @Rest\QueryParam(
     *  name="page",
     *  requirements="\d+",
     *  default="1",
     *  description="The asked page"
     * )
     * @Rest\View()
     * @IsGranted("ROLE_USER")
     */
    public function listProducts(ParamFetcherInterface $paramFetcher, ProductRepository $productRepository)
    {
        $paginator = new Paginator($productRepository);

        return $paginator->getPage($paramFetcher->get('page'), true);
    }

    /**
     * @Rest\Get(
     *  path = "/products/{id}",
     *  name = "show_product",
     *  requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 200)
     * @IsGranted("ROLE_USER")
     */
    public function showProduct(Product $product)
    {
        return $product;
    }

    /**
     * @Rest\Post("/products", name="create_product")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *  "product",
     *  converter="fos_rest.request_body",
     *  options={
     *      "validator"={ "groups"="Create" }
     *  }
     * )
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function createProduct(Product $product, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($product);
        $manager->flush();

        return $this->view($product, Response::HTTP_CREATED, ['Location' => $this->generateUrl('show_product', ['id' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Put(
     *     path = "/products/{id}",
     *     name = "update_product",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newProduct", converter="fos_rest.request_body")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function updateProduct(Product $product, Product $newProduct, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        $product->setName($newProduct->getName());
        $product->setDescription($newProduct->getDescription());
        $product->setPrice($newProduct->getPrice());
        $product->setSerialNumber($newProduct->getSerialNumber());

        $this->getDoctrine()->getManager()->flush();

        return $product;
    }

    /**
     * @Rest\Delete(
     *  path = "/products/{id}",
     *  name = "delete_product",
     *  requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 204)
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function deleteProduct(Product $product)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($product);
        $manager->flush();

        return;
    }
}
