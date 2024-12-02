<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Calculation;
use App\Service\LoanCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalculationController extends AbstractController
{
    
    #[Route('/api/calculations', methods: ['POST'])]
    public function calculate(Request $request, LoanCalculator $calculator, EntityManagerInterface $em, ValidatorInterface $validator, TokenStorageInterface $tokenStorage): JsonResponse
    {
         // Weryfikacja, czy użytkownik jest uwierzytelniony
        
        $data =  $request->getPayload()->all();
        
        $amount = (float) $data['amount'];
        $installments = (int) $data['installments'];
        $interestRate = (float) $data['interest_rate'];

        $schedule = $calculator->calculateSchedule($amount, $installments, $interestRate);
        
          // Oblicz całkowite odsetki
        $totalInterest = array_reduce($schedule, function ($carry, $item) {
            return $carry + $item['interest'];
        }, 0);

        $calculation = new Calculation();
        $calculation->setAmount($amount);
        $calculation->setInstallments($installments);
        $calculation->setInterestRate($interestRate);
        $calculation->setCalculatedAt(new \DateTime());
        $calculation->setSchedule($schedule);
        $calculation->setTotalInterest((float) $totalInterest);

        $violations = $validator->validate($calculation);
       
        if (count($violations) > 0) {
            $errorsString = (string) $violations;
            return new JsonResponse($errorsString, 400);
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

    #[Route('/api/calculations', name: 'get_calculations', methods: ['GET'])]
    public function listCalculations(Request $request, EntityManagerInterface $em, TokenStorageInterface $tokenStorage): JsonResponse
    {
         // Weryfikacja, czy użytkownik jest uwierzytelniony
         $user = $tokenStorage->getToken()->getUser();
         if (!$user || !$this->isGranted('ROLE_USER')) {
             throw new AccessDeniedException('Unauthorized access'); // Jeśli brak uprawnień
         }
        // Pobranie parametru `filter` (domyślnie "all")
        $filter = $request->query->get('filter', 'all');

        // Budowa zapytania do bazy danych
        $queryBuilder = $em->createQueryBuilder()
            ->select('c')
            ->from('App\Entity\Calculation', 'c')
            ->orderBy('c.total_interest', 'DESC')
             // Sortowanie malejąco po sumarycznej kwocie odsetek
            ->setMaxResults(4); // Tylko 4 ostatnie kalkulacje

        if ($filter === 'not_excluded') {
            $queryBuilder->where('c.is_excluded = false'); // Filtruj wyłącznie niewykluczone
        }

        $calculations = $queryBuilder->getQuery()->getResult();
       
        // Formatuj wynik jako JSON
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

    #[Route('/api/calculations/{id}/exclude', name: 'exclude_calculation', methods: ['PATCH'])]
    public function excludeCalculation(int $id, EntityManagerInterface $em): JsonResponse
    {
        //TokenStorageInterface $tokenStorage
        // Autentykacja: sprawdzenie, czy użytkownik jest uprawniony (np. posiada token JWT)
        //$user = $tokenStorage->getToken()->getUser();

        // Tu możesz dodać sprawdzenie, czy użytkownik ma odpowiednie uprawnienia, np. admin.

        // Pobranie kalkulacji z bazy
        $calculation = $em->getRepository(Calculation::class)->find($id);

        if (!$calculation) {
            return $this->json(['error' => 'Calculation not found.'], 404);
        }

        // Zmiana statusu wykluczenia
        $calculation->setIsExcluded(true);

        // Zapisanie zmian w bazie
        $em->persist($calculation);
        $em->flush();

        return new JsonResponse(['message' => 'Calculation excluded successfully.'], 200);
    }

}
