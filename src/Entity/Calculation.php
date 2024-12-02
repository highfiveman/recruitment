<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity]
class Calculation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Assert\NotBlank]
    #[Assert\Type("numeric")]
    #[Assert\Range(['min' => 1000, 'max' => 120000])]
    #[Assert\DivisibleBy(500)]
    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(['min' => 3, 'max' => 120])]
    #[Assert\DivisibleBy(3)]
    private $installments;

    
    #[ORM\Column(type: 'float')]
    private $interest_rate;

    #[ORM\Column(type: 'datetime')]
    private $calculated_at;

    #[ORM\Column(type: 'float')]
    private float $total_interest;

    #[ORM\Column(type: 'boolean')]
    private bool $is_excluded = false;

    #[ORM\Column(type: 'json')]
    private $schedule = [];

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

    public function setCalculatedAt( $calculatedAt)
    {
        return $this->calculated_at = $calculatedAt;
    }

    public function getCalculatedAt()
    {
        return $this->calculated_at;
    }

    public function setSchedule($schedule)
    {
        return $this->schedule = $schedule;
    }

    public function getSchedule()
    {
        return $this->schedule;
    }
  // Getter i setter dla totalInterest
  public function getTotalInterest(): ?float
  {
      return $this->total_interest;
  }

  public function setTotalInterest(float $totalInterest): self
  {
      $this->total_interest = $totalInterest;
      return $this;
  }

  // Getter i setter dla isExcluded
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
