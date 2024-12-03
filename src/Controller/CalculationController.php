<?php

namespace App\Controller;

use App\Entity\Calculation;
use App\Service\LoanCalculator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalculationController extends AbstractController
{

    /**
     * Calculation method for calculating the loan repayment schedule
     * @param Request $request
     * @param LoanCalculator $calculator
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/calculation', methods: ['POST'])]
    public function calculate(Request $request, LoanCalculator $calculator, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {

        $data = $request->getPayload()->all();

        $amount = (float)$data['amount'];
        $installments = (int)$data['installments'];
        $interestRate = (float)$data['interest_rate'];

        $schedule = $calculator->calculateSchedule($amount, $installments, $interestRate);


        $totalInterest = array_reduce($schedule, function ($carry, $item) {
            return $carry + $item['interest'];
        }, 0);

        $calculation = new Calculation();
        $calculation->setAmount($amount);
        $calculation->setInstallments($installments);
        $calculation->setInterestRate($interestRate);
        $calculation->setCalculatedAt(new DateTime());
        $calculation->setSchedule($schedule);
        $calculation->setTotalInterest((float)$totalInterest);

        $violations = $validator->validate($calculation);
       
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
            
                $messages[] = $violation->getMessage() . PHP_EOL;
            }
            return new JsonResponse(['errors' => $messages], 400);
        }

        $em->persist($calculation);
        $em->flush();

        return new JsonResponse([
            'data' => [
                'calculated_at' => $calculation->getCalculatedAt()->format('Y-m-d H:i:s'),
                'amount' => $amount,
                'installments' => $installments,
                'interest_rate' => $interestRate,
            ],
            'schedule' => $schedule,
        ]);
    }

    /**
     * Method of listing calculations (4 last) sorted by total interest amount descending.
     * If you want results only for those who are not excluded, you should pass
     * the parameter filter='not_excluded' in the url
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/listCalculations', name: 'get_calculations', methods: ['GET'])]
    public function listCalculations(Request $request, EntityManagerInterface $em): JsonResponse
    {

        $filter = $request->query->get('filter', 'all');

        $queryBuilder = $em->createQueryBuilder()
            ->select('c')
            ->from('App\Entity\Calculation', 'c')
            ->orderBy('c.total_interest', 'DESC')
            ->setMaxResults(4);

        if ($filter === 'not_excluded') {
            $queryBuilder->where('c.is_excluded = false');
        }

        $calculations = $queryBuilder->getQuery()->getResult();


        $response = array_map(function ($calculation) {
            return [
                'id' => $calculation->getId(),
                'calculated_at' => $calculation->getCalculatedAt()->format('Y-m-d H:i:s'),
                'amount' => $calculation->getAmount(),
                'installments' => $calculation->getInstallments(),
                'interest_rate' => $calculation->getInterestRate(),
                'total_interest' => $calculation->getTotalInterest(),
                'is_excluded' => $calculation->isExcluded(),
            ];
        }, $calculations);

        return new JsonResponse($response, 200);
    }

    /**
     * The function excludes a record in the calculation table
     * @param int $id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/excludeCalculation/{id}', name: 'exclude_calculation', methods: ['PATCH'])]
    public function excludeCalculation(int $id, EntityManagerInterface $em): JsonResponse
    {

        $calculation = $em->getRepository(Calculation::class)->find($id);

        if (!$calculation) {
            return $this->json(['error' => 'Calculation not found.'], 404);
        }

        $calculation->setIsExcluded(true);


        $em->persist($calculation);
        $em->flush();

        return new JsonResponse(['message' => 'Calculation excluded successfully.'], 200);
    }

}
