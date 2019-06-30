<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipationRepository")
 */

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipationRepository")
 * @ORM\Table(name="participations",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="user_event_unique", columns={"user_id", "event_id"})
 *     }
 * )
 */
class Participation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="participations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="participations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;


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


    public function getEvent(): ?Event
    {
        return $this->event;
    }
    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }
}
