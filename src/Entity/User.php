<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $certifiedToken = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCertified = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Talk", mappedBy="author")
     */
    private $userTalks;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * Token you will send in order identify the mail owner
     */
    protected $resetToken;

    /*
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", inversedBy="user")
     */
    private $event;

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }


    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }


    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }


    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }


    public function getCertifiedToken()
    {
        return $this->certifiedToken;
    }

    public function setCertifiedToken($certifiedToken)
    {
        $this->certifiedToken = $certifiedToken;
    }


    public function getIsCertified()
    {
        return $this->isCertified;
    }

    public function setIsCertified($isCertified)
    {
        $this->isCertified = $isCertified;
    }


    public function getUserTalks()
    {
        return $this->userTalks;
    }

    public function setUserTalks($userTalks): void
    {
        $this->userTalks = $userTalks;
    }
}
