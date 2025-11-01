<?php

namespace App\Entity;

use App\Repository\NumberArrayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NumberArrayRepository::class)]
class NumberArray
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $numbers = null;

    #[ORM\Column]
    private ?int $negativeCount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumbers(): ?string
    {
        return $this->numbers;
    }

    public function setNumbers(string $numbers): static
    {
        $this->numbers = $numbers;

        return $this;
    }

    public function getNumbersArray(): array
    {
        if (!$this->numbers) {
            return [];
        }

        $numbers = array_map('trim', explode(',', $this->numbers));
        return array_map('floatval', array_filter($numbers, fn($n) => is_numeric($n)));
    }

    public function setNumbersArray(array $numbers): static
    {
        $this->numbers = implode(', ', $numbers);
        return $this;
    }

    public function getNegativeCount(): ?int
    {
        return $this->negativeCount;
    }

    public function setNegativeCount(int $negativeCount): static
    {
        $this->negativeCount = $negativeCount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function calculateNegativeCount(): void
    {
        $numbers = $this->getNumbersArray();
        $this->negativeCount = count(array_filter($numbers, fn($n) => $n < 0));
    }
}
