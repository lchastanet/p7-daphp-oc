<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("userName")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * 
     * @Serializer\Groups({"list", "details", "edit"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * 
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(
     *  min = 5,
     *  max = 30,
     *  allowEmptyString = true,
     *  groups={"Create", "Modify"}    
     * )
     * 
     * @Serializer\Groups({"list", "details", "edit"})
     */
    private $userName;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(
     *  min = 10,
     *  max = 20,
     *  allowEmptyString = true,
     *  groups={"Create", "Modify"}    
     * )
     * 
     * @Serializer\Groups({"list", "details", "edit"})
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Email(groups={"Create", "Modify"})
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\NotBlank(groups={"Create"})
     * 
     * @Serializer\MaxDepth(1)
     * @Serializer\Groups({"list", "details", "edit"})
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(
     *  min = 10,
     *  max = 50,
     *  allowEmptyString = true,
     *  groups={"Create", "Modify"}    
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="json")
     * 
     * @Serializer\Groups({"details", "edit"})
     */
    private $roles;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of roles
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return $roles;
    }

    /**
     * Set the value of roles
     *
     * @return  self
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }
}
