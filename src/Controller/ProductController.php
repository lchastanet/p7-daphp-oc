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
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


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
     * @Rest\View(
     *  StatusCode = 200,
     *  serializerGroups={"list"},
     *  serializerEnableMaxDepthChecks=true
     * )
     * @IsGranted("ROLE_USER")
     * @OA\Response(
     *  response=200,
     *  description="Returns the paginated list of all products",
     *  @OA\JsonContent(
     *      type="array",
     *      @OA\Items(ref=@Model(type=Product::class, groups={"list"}))
     *  )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="The page you want to load",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *  response=404,
     *  description="The page that you are looking for, does not exist!",
     * )
     * @OA\Response(
     *  response=401,
     *  description="Expired JWT Token | JWT Token not found | Invalid JWT Token",
     * )
     * @OA\Parameter(
     *  name="Authorization",
     *  in="header",
     *  required=true,
     *  description="Bearer Token"
     * )
     * @OA\Tag(name="products")
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
     * @Rest\View(
     *  StatusCode = 200,
     *  serializerGroups={"details"},
     *  serializerEnableMaxDepthChecks=true
     * )
     * @IsGranted("ROLE_USER")
     * @OA\Response(
     *  response=200,
     *  description="Returns the chosen product",
     *  @Model(type=Product::class, groups={"details"})
     * )
     * @OA\Parameter(
     *  name="id",
     *  in="path",
     *  description="ID of the product you want to see",
     *  @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *  response=404,
     *  description="App\\Entity\\Product object not found by the @ParamConverter annotation.",
     * )
     * @OA\Response(
     *  response=401,
     *  description="Expired JWT Token | JWT Token not found | Invalid JWT Token",
     * )
     * @OA\Parameter(
     *  name="Authorization",
     *  in="header",
     *  required=true,
     *  description="Bearer Token"
     * )
     * @OA\Tag(name="products")
     */
    public function showProduct(Product $product)
    {
        return $product;
    }

    /**
     * @Rest\Post("/products", name="create_product")
     * @ParamConverter(
     *  "product",
     *  converter="fos_rest.request_body",
     *  options={
     *      "validator"={ "groups"="Create" }
     *  }
     * )
     * @Rest\View(
     *  StatusCode = 201,
     *  serializerGroups={"details"},
     *  serializerEnableMaxDepthChecks=true
     * )
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @OA\Response(
     *  response=201,
     *  description="Returns created product",
     *  @Model(type=Product::class, groups={"details"})
     * )
     * @OA\Parameter(
     *  name="Product",
     *  in="query",
     *  @Model(type=Product::class, groups={"edit"}),
     *  required=true,
     *  description="The product object"
     * )
     * @OA\Response(
     *  response=400,
     *  description="The JSON sent contains invalid data. Here are the errors you need to correct: Field {property}: {message}"
     * )
     * @OA\Parameter(
     *  name="Authorization",
     *  in="header",
     *  required=true,
     *  description="Bearer Token"
     * )
     * @OA\Response(
     *  response=401,
     *  description="Expired JWT Token | JWT Token not found | Invalid JWT Token",
     * )
     * @OA\Tag(name="products")
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
     * @Rest\Put(
     *     path = "/products/{id}",
     *     name = "update_product",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newProduct", converter="fos_rest.request_body")
     * @Rest\View(
     *  StatusCode = 200,
     *  serializerGroups={"edit"},
     *  serializerEnableMaxDepthChecks=true
     * )
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @OA\Response(
     *  response=200,
     *  description="Returns modified product",
     *  @Model(type=Product::class, groups={"details"})
     * )
     * @OA\Parameter(
     *  name="Product",
     *  in="query",
     *  @Model(type=Product::class, groups={"edit"}),
     *  required=true,
     *  description="The product object"
     * )
     * @OA\Parameter(
     *  name="id",
     *  in="path",
     *  description="ID of the product you want to modify",
     *  @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *  response=400,
     *  description="The JSON sent contains invalid data. Here are the errors you need to correct: Field {property}: {message}"
     * )
     * @OA\Parameter(
     *  name="Authorization",
     *  in="header",
     *  required=true,
     *  description="Bearer Token"
     * )
     * @OA\Response(
     *  response=401,
     *  description="Expired JWT Token | JWT Token not found | Invalid JWT Token",
     * )
     * @OA\Response(
     *  response=404,
     *  description="App\\Entity\\Product object not found by the @ParamConverter annotation.",
     * )
     * @OA\Tag(name="products")
     */
    public function updateProduct(Product $product, Product $newProduct, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        if ($newProduct->getName()) {
            $product->setName($newProduct->getName());
        }

        if ($newProduct->getDescription()) {
            $product->setDescription($newProduct->getDescription());
        }

        if ($newProduct->getPrice()) {
            $product->setPrice($newProduct->getPrice());
        }

        if ($newProduct->getSerialNumber()) {
            $product->setSerialNumber($newProduct->getSerialNumber());
        }

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
     * @OA\Response(
     *  response=204,
     *  description="Returns an empty object",
     *  @Model(type=Product::class, groups={"deleted"})
     * )
     * @OA\Parameter(
     *  name="id",
     *  in="path",
     *  description="ID of the product you want to delete",
     *  @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *  response=404,
     *  description="App\\Entity\\Product object not found by the @ParamConverter annotation.",
     * )
     * @OA\Response(
     *  response=401,
     *  description="Expired JWT Token | JWT Token not found | Invalid JWT Token",
     * )
     * @OA\Parameter(
     *  name="Authorization",
     *  in="header",
     *  required=true,
     *  description="Bearer Token"
     * )
     * @OA\Response(
     *  response=403,
     *  description="Access denied.",
     * )
     * @OA\Tag(name="products")
     */
    public function deleteProduct(Product $product)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($product);
        $manager->flush();

        return;
    }
}
