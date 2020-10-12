<?php

namespace App\Controller;

use App\Entity\Client;
use App\Service\Paginator;
use App\Repository\ClientRepository;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    use ControllerTrait;

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
}
