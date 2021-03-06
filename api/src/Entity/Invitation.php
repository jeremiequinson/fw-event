<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\EntityHook\AutoCreatedAtInterface;
use App\EntityHook\AutoUpdatedAtInterface;
use App\Helpers\DateHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use App\Api\Filter\ExpiredInvitationFilter;
use App\Validator\NotEventAuthor;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * @ORM\Entity(repositoryClass="App\Repository\InvitationRepository")
 * @UniqueEntity(
 *     fields={"event", "recipient"},
 *     errorPath="recipient",
 *     message="User is already invited to this event"
 * )
 * @Assert\Callback(callback="validate", groups={"validation:invitation:confirm"})
 * @Assert\Callback(callback="validateAuthor", groups={"Default"})
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(name="uq_event_recipient_idx", columns={"event_id", "recipient_id"})}
 * )
 * @ApiResource(
 *      collectionOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"invitation:read", "invitation:read:event", "invitation:read:user"}},
 *          },
 *          "post"={
 *              "defaults"={"confirmed"=false},
 *              "denormalization_context"={"groups"={"invitation:post"}},
 *              "validation_groups"={"validate:invitation:post", "Default"},
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getEvent().getOrganizer() == user)"
 *          }
 *     },
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"invitation:read", "invitation:read:event", "invitation:read:user"}},
 *          },
 *          "put"={
 *              "denormalization_context"={"groups"={"invitation:put"}},
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getEvent().getOrganizer() == user)"
 *          },
 *          "confirm"={
 *              "method"="PUT",
 *              "path"="/invitations/{id}/confirm",
 *              "denormalization_context"={"groups"={"invitation:confirm"}},
 *              "validation_groups"={"validation:invitation:confirm", "Default"},
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getRecipient() == user)"
 *          },
 *          "delete"
 *      },
 *      attributes={
 *          "normalization_context"={"groups"={"invitation:read"}},
 *          "pagination_client_items_per_page"=true,
 *          "maximum_items_per_page"=100
 *     },
 *     subresourceOperations={
 *          "api_events_participants_get_subresource"={
 *              "method"="get",
 *              "normalization_context"={"groups"={"event:read:invitation"}}
 *          },
 *          "api_users_invitations_get_subresource"={
 *              "method"="get",
 *              "normalization_context"={"groups"={"user:read:invitation", "user:read:event"}}
 *          }
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "event": "exact",
 *     "recipient": "exact",
 * })
 * @ApiFilter(DateFilter::class, properties={"expireAt"}, strategy=DateFilter::EXCLUDE_NULL)
 * @ApiFilter(ExpiredInvitationFilter::class)
 * @ApiFilter(BooleanFilter::class, properties={"confirmed"})
 * @ApiFilter(OrderFilter::class, properties={"id", "rate", "createdAt"}, arguments={"orderParameterName"="order"})
 */
class Invitation implements AutoCreatedAtInterface, AutoUpdatedAtInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"invitation:read", "user:read:invitation", "event:read:invitation", "user:read:invitation"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"invitation:read", "user:read:invitation", "invitation:post", "user:read:invitation"})
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"invitation:read", "invitation:post", "event:read:invitation"})
     * @Assert\NotBlank()
     */
    private $recipient;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"invitation:read", "invitation:confirm", "user:read:invitation", "event:read:invitation"})
     */
    private $confirmed;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"invitation:read", "user:read:invitation", "invitation:post", "invitation:put", "event:read:invitation", "user:read:invitation"})
     * @Assert\GreaterThan("today", groups={"validate:invitation:post"})
     */
    private $expireAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"invitation:read", "user:read:invitation", "event:read:invitation", "user:read:invitation"})
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

    public function __construct()
    {
        $this->confirmed = false;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getExpireAt(): ?\DateTimeInterface
    {
        return $this->expireAt;
    }

    public function setExpireAt(?\DateTimeInterface $expireAt): self
    {
        $this->expireAt = $expireAt;

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

    /**
     * @Groups({"invitation:read", "event:read:invitation"})
     */
    public function isExpired()
    {
        if ($this->expireAt instanceof \DateTime) {
            return DateHelper::getToday() > $this->expireAt;
        }
        return DateHelper::getToday() > $this->getEvent()->getEndAt();
    }




    /**
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->confirmed && $this->isExpired()) {
            $context
                ->buildViolation('Your invitation has expired')
                ->atPath('confirmed')
                ->addViolation()
            ;
        }
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validateAuthor(ExecutionContextInterface $context)
    {
        if ($this->getRecipient()->getId() === $this->getEvent()->getOrganizer()->getId()) {
            $context
                ->buildViolation('Cannot invite author of the event')
                ->atPath('recipient')
                ->addViolation()
            ;
        }
    }
}
