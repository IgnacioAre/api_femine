<?php

namespace App\Entity;

use App\Repository\UsersCardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsersCardRepository::class)
 */
class UsersCard
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="usersCards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class, inversedBy="usersCards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $card;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="smallint")
     */
    private $stars;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $giftcard_message;

    /**
     * @ORM\Column(type="boolean")
     */
    private $giftcard_status;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCard(): ?card
    {
        return $this->card;
    }

    public function setCard(?card $card): self
    {
        $this->card = $card;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(int $stars): self
    {
        $this->stars = $stars;

        return $this;
    }

    public function getGiftcardMessage(): ?string
    {
        return $this->giftcard_message;
    }

    public function setGiftcardMessage(?string $giftcard_message): self
    {
        $this->giftcard_message = $giftcard_message;

        return $this;
    }

    public function isGiftcardStatus(): ?bool
    {
        return $this->giftcard_status;
    }

    public function setGiftcardStatus(bool $giftcard_status): self
    {
        $this->giftcard_status = $giftcard_status;

        return $this;
    }

}
