<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\DayType;
use App\Form\ExpenseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Expense;

class ExpenseController extends AbstractController
{
    #[Route('/', name: 'back')]
    public function redirectToDashboard(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->json([
            'id' => 1,
            'categoryId' => 'Test',
        ]);
    }

    #[Route('/expenses', name: 'expense_index')]
    public function index(
        ManagerRegistry $doctrine,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $userId = $user->getId();

        $date = date("Y-m-d");

        $expense = new Expense();

        $form = $this->createForm(ExpenseType::class, $expense, ['label' => 'Add expense']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $expense = $form->getData();
            $expense->setUserId($userId);

            $entityManager->persist($expense);
            $entityManager->flush();
        }

        $major = $entityManager->getRepository(Expense::class)->prepareExpensesByDate('major', $userId, $date);
        $home = $entityManager->getRepository(Expense::class)->prepareExpensesByDate('home', $userId, $date);
        $daily = $entityManager->getRepository(Expense::class)->prepareExpensesByDate('daily', $userId, $date);

        return $this->render(
            'expense/list.html.twig',
            [
                'form' => $form,
                'daily' => $daily,
                'major' => $major,
                'home' => $home,
                'userId' => $userId,
                'date' => date("Y-m-d")
            ]
        );
    }

    #[Route('/expenses-by-date', name: 'expense_by_date')]
    public function getExpensesByDate(
        #[MapQueryParameter] ?string $day,
        #[MapQueryParameter] ?string $month,
        #[MapQueryParameter] ?string $year,
        ManagerRegistry $doctrine,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$day && !$month && !$year) {
            $date = date("Y-m-d");
        } else {
            $date = $year . '-' . $month . '-' . $day;
        }

        $user = $this->getUser();
        $userId = $user->getId();

        $major = $doctrine->getRepository(Expense::class)->prepareExpensesByDate('major', $userId, $date);
        $home = $doctrine->getRepository(Expense::class)->prepareExpensesByDate('home', $userId, $date);
        $daily = $doctrine->getRepository(Expense::class)->prepareExpensesByDate('daily', $userId, $date);

        $form = $this->createForm(DayType::class, ['date' => $date]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();
            $formattedDate = explode('-', $date);

            return $this->redirectToRoute(
                'expense_by_date',
                ['year' => $formattedDate[0], 'month' => $formattedDate[1], 'day' => $formattedDate[2]]
            );
        }

        return $this->render(
            'expense/byDate.html.twig',
            ['daily' => $daily, 'major' => $major, 'home' => $home, 'userId' => $userId, 'form' => $form]
        );
    }

    #[Route('/expenses/api', name: 'expense_api')]
    public function expensesApi(
        ManagerRegistry $doctrine,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $parameters = json_decode($request->getContent(), true);

        $expenses = $entityManager->getRepository(Expense::class)->findBy(['userId' => $parameters['userId']]);

        $expenses = array_map(function ($expense) use ($doctrine) {
            return [
                'id' => $expense->getId(),
                'date' => $expense->getDate(),
                'type' => $expense->getType(),
                'description' => $expense->getDescription(),
                'categoryId' => $doctrine->getRepository(Category::class)->find(
                    $expense->getCategoryId()
                )->getName(),
                'spending' => $expense->getSpending(),
            ];
        }, $expenses);

        return $this->json(json_encode($expenses));
    }

    #[Route('/expenses/{id}', name: 'expense_update', methods: ['get'])]
    public function update(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $userId = $user->getId();

        $entityManager = $doctrine->getManager();
        $expense = $entityManager->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->render('No expense found for id ' . $id);
        }

        $form = $this->createForm(ExpenseType::class, $expense, ['method' => 'get', 'label' => 'Update expense']);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $expense = $form->get('categoryId')->getData();

            if ($form->isValid()) {
                $expense = $form->getData();
                $expense->setUserId($userId);

                $entityManager->persist($expense);
                $entityManager->flush();
            }
        }

        return $this->render('expense/edit.html.twig', ['expense' => $expense, 'form' => $form, 'userId' => $userId]);
    }

//    #[Route('/expenses/{id}', name: 'expense_delete', methods: ['delete'])]
//    public function delete(ManagerRegistry $doctrine, int $id): JsonResponse
//    {
//        $entityManager = $doctrine->getManager();
//        $expense = $entityManager->getRepository(Expense::class)->find($id);
//
//        if (!$expense) {
//            return $this->json('No expense found for id' . $id, 404);
//        }
//
//        $entityManager->remove($expense);
//        $entityManager->flush();
//
//        return $this->json('Deleted a expense successfully with id ' . $id);
//    }
}
