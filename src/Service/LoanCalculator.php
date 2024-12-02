<?php
namespace App\Service;

class LoanCalculator
{
    public function calculateSchedule(float $amount, int $installments, float $interestRate): array
    {
        $monthlyRate = $interestRate / 12 / 100;
        $installmentValue = $amount * $monthlyRate / (1 - pow(1 + $monthlyRate, -$installments));
        $schedule = [];
        $remainingAmount = $amount;

        for ($i = 1; $i <= $installments; $i++) {
            $interest = $remainingAmount * $monthlyRate;
            $principal = $installmentValue - $interest;
            $remainingAmount -= $principal;

            $schedule[] = [
                'installment_number' => $i,
                'installment_value' => round($installmentValue, 2),
                'interest' => round($interest, 2),
                'principal' => round($principal, 2),
            ];
        }

        return $schedule;
    }
}
