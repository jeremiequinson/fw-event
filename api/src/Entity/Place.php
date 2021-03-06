<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\EntityHook\AutoCreatedAtInterface;
use App\EntityHook\AutoUpdatedAtInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;



/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write_place"}},
 *     attributes={
 *          "pagination_client_items_per_page"=true,
 *          "maximum_items_per_page"=100
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\PlaceRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="A place {{ value }} already exists"
 * )
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(name="uq_name_idx", columns={"name"})}
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "name": "partial",
 *     "streetNumber": "partial",
 *     "city": "partial",
 *     "streetName": "partial",
 *     "postalCode": "partial",
 *     "country": "partial",
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "createdAt"}, arguments={"orderParameterName"="order"})
 */
class Place implements AutoCreatedAtInterface, AutoUpdatedAtInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read", "event:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=175)
     * @Groups({"read", "event:read", "write_place", "event:write"})
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "event:read", "write_place", "event:write"})
     * @Assert\NotBlank
     */
    private $streetNumber;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "event:read", "write_place", "event:write"})
     * @Assert\NotBlank
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "event:read", "write_place", "event:write"})
     * @Assert\NotBlank
     */
    private $streetName;

    /**
     * @ORM\Column(type="string", length=16)
     * @Groups({"read", "event:read", "write_place", "event:write"})
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=3,
     *     max=16
     * )
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "event:read", "write_place", "event:write"})
     * @Assert\NotBlank
     */
    private $country;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read", "event:read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"admin:user:read"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"admin:user:read"})
     */
    private $deletedAt;

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

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(?string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
