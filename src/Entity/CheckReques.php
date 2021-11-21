<?php

namespace App\Entity;

use App\Repository\CheckRequesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CheckRequesRepository::class)
 */
class CheckReques
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $user_ip;

    /**
     * @ORM\Column(type="integer")
     */
    private $time_last_request;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $user_email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIp(): ?string
    {
        return $this->user_ip;
    }

    public function setUserIp(string $user_ip): self
    {
        $this->user_ip = $user_ip;

        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->user_email;
    }

    public function setUserEmail(string $user_email): self
    {
        $this->user_email = $user_email;

        return $this;
    }
    public function getCreatedTime(): ?int
    {
        return $this->time_last_request;
    }

    public function setCreatedTime(int $time_last_request): self
    {
        $this->time_last_request = $time_last_request;

        return $this;
    }
}
