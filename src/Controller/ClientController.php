<?php

namespace App\Controller;

use App\Entity\Client;
use App\Service\Paginator;
use OpenApi\Annotations as OA;
use App\Service\ViolationsChecker;
use App\Repository\ClientRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\ConstraintViolationList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;



class ClientController extends AbstractController
{
    use ControllerTrait;
    use ViolationsChecker;

    /**
     * @Rest\Get("/clients", name="list_clients")
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
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @OA\Response(
     *  response=200,
     *  description="Returns the paginated list of all clients",
     *  @OA\JsonContent(
     *      type="array",
     *      @OA\Items(ref=@Model(type=Client::class, groups={"list"}))
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
     * @OA\Tag(name="clients")
     */
    public function listClients(ParamFetcherInterface $paramFetcher, ClientRepository $clientRepository)
    {
        $paginator = new Paginator($clientRepository);

        return $paginator->getPage($paramFetcher->get('page'), true);
    }

    /**
     * @Rest\Get(
     *  path = "/clients/{id}",
     *  name = "show_client",
     *  requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *  StatusCode = 200,
     *  serializerGroups={"details"},
     *  serializerEnableMaxDepthChecks=true
     * )
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @OA\Response(
     *  response=200,
     *  description="Returns the chosen client",
     *  @Model(type=Client::class, groups={"details"})
     * )
     * @OA\Parameter(
     *  name="id",
     *  in="path",
     *  description="ID of the client you want to see",
     *  @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *  response=404,
     *  description="App\\Entity\\Client object not found by the @ParamConverter annotation.",
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
     * @OA\Tag(name="clients")
     */
    public function showClient(Client $client)
    {
        return $client;
    }

    /**
     * @Rest\Post("/clients", name="create_client")
     * @ParamConverter(
     *  "client",
     *  converter="fos_rest.request_body",
     *  options={
     *      "validator"={ "groups"="Create" }
     *  }
     * )
     * @Rest\View(
     *  StatusCode = 201,
     *  serializerGroups={"details"}
     * )
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @OA\Response(
     *  response=201,
     *  description="Returns created client",
     *  @Model(type=Client::class, groups={"details"})
     * )
     * @OA\Parameter(
     *  name="Client",
     *  in="query",
     *  @Model(type=Client::class, groups={"create"}),
     *  required=true,
     *  description="The client object"
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
     * @OA\Tag(name="clients")
     */
    public function createClient(Client $client, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($client);
        $manager->flush();

        return $client;
    }

    /**
     * @Rest\Put(
     *     path = "/clients/{id}",
     *     name = "update_client",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newClient", converter="fos_rest.request_body")
     * @Rest\View(
     *  StatusCode = 200,
     *  serializerGroups={"edit"},
     *  serializerEnableMaxDepthChecks=true
     * )
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @OA\Response(
     *  response=200,
     *  description="Returns modified client",
     *  @Model(type=Client::class, groups={"details"})
     * )
     * @OA\Parameter(
     *  name="Client",
     *  in="query",
     *  @Model(type=Client::class, groups={"create"}),
     *  required=true,
     *  description="The client object"
     * )
     * @OA\Parameter(
     *  name="id",
     *  in="path",
     *  description="ID of the client you want to see",
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
     *  description="App\\Entity\\Client object not found by the @ParamConverter annotation.",
     * )
     * @OA\Tag(name="clients")
     */
    public function updateClient(Client $client, Client $newClient, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        $client->setName($newClient->getName());
        $client->setDescription($newClient->getDescription());
        $client->setPhoneNumber($newClient->getPhoneNumber());
        $client->setAddress($newClient->getAddress());

        $this->getDoctrine()->getManager()->flush();

        return $client;
    }

    /**
     * @Rest\Delete(
     *  path = "/clients/{id}",
     *  name = "delete_client",
     *  requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 204)
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @OA\Response(
     *  response=204,
     *  description="Returns an empty object"
     * )
     * @OA\Parameter(
     *  name="id",
     *  in="path",
     *  description="ID of the client you want to delete",
     *  @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *  response=404,
     *  description="App\\Entity\\Client object not found by the @ParamConverter annotation.",
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
     * @OA\Tag(name="clients")
     */
    public function deleteClient(Client $client)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($client);
        $manager->flush();

        return;
    }
}
