<?php

namespace App\Controller;

use App\Entity\Client;
use App\Service\Paginator;
use App\Service\ViolationsChecker;
use App\Repository\ClientRepository;
use FOS\RestBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @Rest\View()
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
     * @Rest\View(StatusCode = 200)
     */
    public function showClient(Client $client)
    {
        return $client;
    }

    /**
     * @Rest\Post("/clients", name="create_client")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *  "client",
     *  converter="fos_rest.request_body",
     *  options={
     *      "validator"={ "groups"="Create" }
     *  }
     * )
     */
    public function createProduct(Client $client, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($client);
        $manager->flush();

        return $this->view($client, Response::HTTP_CREATED, ['Location' => $this->generateUrl('show_client', ['id' => $client->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Put(
     *     path = "/clients/{id}",
     *     name = "update_client",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newClient", converter="fos_rest.request_body")
     */
    public function updateProduct(Client $client, Client $newClient, ConstraintViolationList $violations)
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
     */
    public function deleteProduct(Client $client)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($client);
        $manager->flush();

        return;
    }
}
