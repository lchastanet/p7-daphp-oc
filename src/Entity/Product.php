<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
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
     * @ORM\Column(type="decimal", precision=6, scale=2)
     * 
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Positive(groups={"Create"})
     * 
     * @Serializer\Groups({"list", "details", "edit"})
     */
    private $price;

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
     * 
     * @Serializer\Groups({"list", "details", "edit"})
     */
    private $serialNumber;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }
}
