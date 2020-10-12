<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\Paginator;
use App\Repository\ProductRepository;
use FOS\RestBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Exception\ResourceValidationException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProductController extends AbstractController
{
    use ControllerTrait;

    /**
     * @Rest\Get("/products", name="list_products")
     * @Rest\QueryParam(
     *  name="page",
     *  requirements="\d+",
     *  default="1",
     *  description="The asked page"
     * )
     * @Rest\View()
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
     */
    public function createProduct(Product $product, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

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
     */
    public function updateProduct(Product $product, Product $newProduct, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

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
     */
    public function deleteProduct(Product $product)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($product);
        $manager->flush();

        return;
    }
}
