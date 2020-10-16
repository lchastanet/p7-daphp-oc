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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Security as SecurityFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


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
     * @Rest\View(StatusCode = 200)
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")
     */
    public function listUsers(ParamFetcherInterface $paramFetcher, UserRepository $userRepository, SecurityFilter $security)
    {
        $paginator = new Paginator($userRepository);

        if (in_array("ROLE_SUPER_ADMIN", $security->getUser()->getRoles())) {
            return $paginator->getPage($paramFetcher->get('page'), true);
        }

        $loggedUser = $userRepository->findOneBy(["userName" => $security->getUser()->getUsername()]);

        $clientId = $loggedUser->getClient()->getId();

        return $paginator->getPage($paramFetcher->get('page'), true, ['client' => $clientId]);
    }

    /**
     * @Rest\Get(
     *  path = "/users/{id}",
     *  name = "show_user",
     *  requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 200)
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")
     */
    public function showUser(User $user, UserRepository $userRepository, SecurityFilter $security)
    {
        if (in_array("ROLE_SUPER_ADMIN", $security->getUser()->getRoles())) {
            return $user;
        }

        $loggedUser = $userRepository->findOneBy(["userName" => $security->getUser()->getUsername()]);

        if ($loggedUser->getClient()->getId() != $user->getClient()->getId()) {
            throw new AccessDeniedException();
        }

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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")
     */
    public function createUser(User $user, UserRepository $userRepository, ConstraintViolationList $violations, SecurityFilter $security, UserPasswordEncoderInterface $encoder)
    {
        $this->checkViolations($violations);

        if (!in_array("ROLE_SUPER_ADMIN", $security->getUser()->getRoles())) {
            $loggedUser = $userRepository->findOneBy(["userName" => $security->getUser()->getUsername()]);

            $user->setClient($loggedUser->getClient());
            $user->setRoles(["ROLE_USER"]);
        }

        if (empty($user->getRoles())) {
            $user->setRoles(["ROLE_USER"]);
        }

        $manager = $this->getDoctrine()->getManager();

        $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")
     */
    public function updateUser(User $user, User $newUser, ConstraintViolationList $violations, SecurityFilter $security, UserRepository $userRepository)
    {
        $this->checkViolations($violations);

        if (!in_array("ROLE_SUPER_ADMIN", $security->getUser()->getRoles())) {
            $loggedUser = $userRepository->findOneBy(["userName" => $security->getUser()->getUsername()]);

            if ($loggedUser->getClient()->getId() != $user->getClient()->getId()) {
                throw new AccessDeniedException("You don't have the rights for modifying this user.");
            }

            $user->setRoles(["ROLE_USER"]);
        } else {
            $user->setRoles($newUser->getRoles());
        }

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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")
     */
    public function deleteUser(User $user, SecurityFilter $security, UserRepository $userRepository)
    {
        if (!in_array("ROLE_SUPER_ADMIN", $security->getUser()->getRoles())) {
            $loggedUser = $userRepository->findOneBy(["userName" => $security->getUser()->getUsername()]);

            if ($loggedUser->getClient()->getId() != $user->getClient()->getId()) {
                throw new AccessDeniedException("You don't have the rights for deleting this user.");
            }
        }

        $manager = $this->getDoctrine()->getManager();

        $manager->remove($user);
        $manager->flush();

        return;
    }
}
