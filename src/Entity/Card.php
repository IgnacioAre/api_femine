<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $days_limit;

    /**
     * @ORM\Column(type="smallint")
     */
    private $stars;

    /**
     * @ORM\OneToMany(targetEntity=UsersCard::class, mappedBy="card")
     */
    private $usersCards;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 1})
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    public function __construct()
    {
        $this->usersCards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDaysLimit(): ?int
    {
        return $this->days_limit;
    }

    public function setDaysLimit(int $days_limit): self
    {
        $this->days_limit = $days_limit;

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

    /**
     * @return Collection<int, UsersCard>
     */
    public function getUsersCards(): Collection
    {
        return $this->usersCards;
    }

    public function addUsersCard(UsersCard $usersCard): self
    {
        if (!$this->usersCards->contains($usersCard)) {
            $this->usersCards[] = $usersCard;
            $usersCard->setCard($this);
        }

        return $this;
    }

    public function removeUsersCard(UsersCard $usersCard): self
    {
        if ($this->usersCards->removeElement($usersCard)) {
            // set the owning side to null (unless already changed)
            if ($usersCard->getCard() === $this) {
                $usersCard->setCard(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
