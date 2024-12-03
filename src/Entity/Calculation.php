<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity]
class Calculation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[Assert\NotBlank]
    #[Assert\Type("numeric")]
    #[Assert\Range(['min' => 1000, 'max' => 12000])]
    #[Assert\DivisibleBy(500)]
    #[ORM\Column(type: 'float')]
    private float $amount;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(['min' => 3, 'max' => 18])]
    #[Assert\DivisibleBy(3)]
    private int $installments;

    
    #[ORM\Column(type: 'float')]
    private float $interest_rate;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $calculated_at;

    #[ORM\Column(type: 'float')]
    private float $total_interest;

    #[ORM\Column(type: 'boolean')]
    private bool $is_excluded = false;

    #[ORM\Column(type: 'json')]
    private array $schedule = [];

    // Getters and Setters ...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAmount(float $amount): ?float
    {
        return $this->amount = $amount;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setInstallments(int $installments): ?int
    {
        return $this->installments = $installments;
    }

    public function getInstallments(): ?int
    {
        return $this->installments;
    }

    public function setInterestRate(int $interest_rate): ?float
    {
        return $this->interest_rate = $interest_rate;
    }

    public function getInterestRate(): ?float
    {
        return $this->interest_rate;
    }

    public function setCalculatedAt( $calculatedAt): \DateTime
    {
        return $this->calculated_at = $calculatedAt;
    }

    public function getCalculatedAt(): \DateTime
    {
        return $this->calculated_at;
    }

    public function setSchedule($schedule)
    {
        return $this->schedule = $schedule;
    }

    public function getSchedule(): array
    {
        return $this->schedule;
    }

  public function getTotalInterest(): ?float
  {
      return $this->total_interest;
  }

  public function setTotalInterest(float $totalInterest): self
  {
      $this->total_interest = $totalInterest;
      return $this;
  }

  public function isExcluded(): bool
  {
      return $this->is_excluded;
  }

  public function setIsExcluded(bool $isExcluded): self
  {
      $this->is_excluded = $isExcluded;
      return $this;
  }
}
