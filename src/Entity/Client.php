<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client
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
     * @ORM\Column(type="string", length=255)
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(
     *  min = 10,
     *  max = 100,
     *  allowEmptyString = true,
     *  groups={"Create", "Modify"}    
     * )
     * 
     * @Serializer\Groups({"list", "details", "edit"})
     */
    private $address;

    /**
     * @ORM\Column(type="text")
     * 
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(
     *  min = 20,
     *  max = 200,
     *  allowEmptyString = true,
     *  groups={"Create", "Modify"}    
     * )
     * 
     * @Serializer\Groups({"list", "details", "edit"}) 
     */
    private $description;

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
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="client", orphanRemoval=true)
     * 
     * @Serializer\Groups({"details"})
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setClient($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getClient() === $this) {
                $user->setClient(null);
            }
        }

        return $this;
    }
}
