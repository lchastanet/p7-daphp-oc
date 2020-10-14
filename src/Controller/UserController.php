<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Paginator;
use App\Repository\UserRepository;
use App\Service\ViolationsChecker;
use FOS\RestBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    use ControllerTrait;
    use ViolationsChecker;

    /**
     * @Rest\Get("/users", name="list_users")
     * @Rest\QueryParam(
     *  name="page",
     *  requirements="\d+",
     *  default="1",
     *  description="The asked page"
     * )
     * @Rest\View()
     */
    public function listUsers(ParamFetcherInterface $paramFetcher, UserRepository $userRepository)
    {
        $paginator = new Paginator($userRepository);

        return $paginator->getPage($paramFetcher->get('page'), true);
    }

    /**
     * @Rest\Get(
     *  path = "/users/{id}",
     *  name = "show_user",
     *  requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 200)
     */
    public function showUser(User $user)
    {
        return $user;
    }

    /**
     * @Rest\Post("/users", name="create_user")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *  "user",
     *  converter="fos_rest.request_body",
     *  options={
     *      "validator"={ "groups"="Create" }
     *  }
     * )
     */
    public function createUser(User $user, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($user);
        $manager->flush();

        return $this->view($user, Response::HTTP_CREATED, ['Location' => $this->generateUrl('show_user', ['id' => $user->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Put(
     *     path = "/users/{id}",
     *     name = "update_user",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newUser", converter="fos_rest.request_body")
     */
    public function updateUser(User $user, User $newUser, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        $user->setUserName($newUser->getUserName());
        $user->setPassword($newUser->getPassword());
        $user->setEmail($newUser->getEmail());
        $user->setPhoneNumber($newUser->getPhoneNumber());
        $user->setClient($newUser->getClient());

        $this->getDoctrine()->getManager()->flush();

        return $user;
    }

    /**
     * @Rest\Delete(
     *  path = "/users/{id}",
     *  name = "delete_user",
     *  requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 204)
     */
    public function deleteUser(User $user)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($user);
        $manager->flush();

        return;
    }
}
