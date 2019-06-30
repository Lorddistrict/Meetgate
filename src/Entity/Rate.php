<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RateRepository")
 * @ORM\Table(name="rates",
     uniqueConstraints={
       @ORM\UniqueConstraint(name="user_talk_unique", columns={"user_id", "talk_id"})
     }
   )
 */
class Rate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="rates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Talk", inversedBy="rates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $talk;

    /**
     * @ORM\Column(type="smallint")
     */
    private $stars;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


    public function getTalk(): ?Talk
    {
        return $this->talk;
    }
    public function setTalk(?Talk $talk): self
    {
        $this->talk = $talk;

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
}
