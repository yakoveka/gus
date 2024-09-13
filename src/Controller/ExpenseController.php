<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategorySearchType;
use App\Form\DayType;
use App\Form\ExpenseType;
use DateTime;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExpenseController extends AbstractController
{
    #[Route('/', name: 'back')]
    public function redirectToDashboard(): JsonResponse
    {
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
        $user = $this->getUser();
        $userId = $user->getId();

        $date = date("Y-m-d");

        $expense = new Expense();
        $expense->setDate($date);

        $form = $this->createForm(ExpenseType::class, $expense, ['label' => 'Add expense']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $expense = $form->getData();
            $expense->setUserId($userId);

            $entityManager->persist($expense);
            $entityManager->flush();

            unset($expense);
            unset($form);

            $expense = new Expense();
            $expense->setDate($date);

            $form = $this->createForm(ExpenseType::class, $expense, ['label' => 'Add expense']);
        }

        $expenses = $entityManager->getRepository(Expense::class)->prepareExpensesByDate($userId, $date);

        return $this->render(
            'expense/list.html.twig',
            [
                'form' => $form,
                'expenses' => $expenses,
                'userId' => $userId,
            ]
        );
    }

    /**
     * @throws \Exception
     */
    #[Route('/expenses-by-date', name: 'expense_by_date')]
    public function getExpensesByDate(
        #[MapQueryParameter] ?string $day,
        #[MapQueryParameter] ?string $month,
        #[MapQueryParameter] ?string $year,
        ManagerRegistry $doctrine,
        Request $request,
    ): Response {
        if (!$day && !$month && !$year) {
            $date = date("Y-m-d");
        } else {
            $date = $year . '-' . $month . '-' . $day;
        }

        $user = $this->getUser();
        $userId = $user->getId();

        $expenses = $doctrine->getRepository(Expense::class)->prepareExpensesByDate($userId, $date);

        $form = $this->createForm(DayType::class, ['date' => $date]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();

            if ($form->get('next')->isClicked()) {
                $date = new DateTime($date);
                $date = $date->modify('+1 day')->format('Y-m-d');
            } elseif ($form->get('previous')->isClicked()) {
                $date = new DateTime($date);
                $date = $date->modify('-1 day')->format('Y-m-d');
            }

            $formattedDate = explode('-', $date);

            return $this->redirectToRoute(
                'expense_by_date',
                ['year' => $formattedDate[0], 'month' => $formattedDate[1], 'day' => $formattedDate[2]]
            );
        }

        return $this->render(
            'expense/byDate.html.twig',
            ['expenses' => $expenses, 'userId' => $userId, 'form' => $form]
        );
    }

    /**
     * @throws \Exception
     */
    #[Route('/expenses-by-category', name: 'expense_by_category')]
    public function getExpensesByCategory(
        #[MapQueryParameter] ?string $type,
        #[MapQueryParameter] ?int $categoryId,
        ManagerRegistry $doctrine,
        Request $request,
    ): Response {
        $user = $this->getUser();
        $userId = $user->getId();

        if (!$type) {
            $type = 'daily';
        }

        if (!$categoryId) {
            $categories = $doctrine->getRepository(Category::class)->findBy(['userId' => $userId, 'type' => $type]);
            $category = reset($categories);
            $categoryId = $category->getId();
        }

        $expenses = $doctrine->getRepository(Expense::class)->prepareExpensesByCategoryId($userId, $type, $categoryId);

        $form = $this->createForm(CategorySearchType::class, ['type' => $type, 'categoryId' => $categoryId]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $form->get('type')->getData();
            $categoryId = $form->get('categoryId')->getData();

            return $this->redirectToRoute(
                'expense_by_category',
                ['type' => $type, 'categoryId' => $categoryId]
            );
        }

        return $this->render(
            'expense/byCategory.html.twig',
            ['expenses' => $expenses, 'userId' => $userId, 'form' => $form]
        );
    }

    #[Route('/api/expenses', name: 'api_all_expenses')]
    public function apiAllExpenses(
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

        $route = $request->headers->get('referer');

        return $this->render('expense/edit.html.twig', ['expense' => $expense, 'form' => $form, 'userId' => $userId, 'backLink' => $route]);
    }

    #[Route('/expenses/delete/{id}', name: 'expense_delete', methods: ['get'])]
    public function delete(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $expense = $entityManager->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->render('No expense found for id' . $id);
        }

        $entityManager->remove($expense);
        $entityManager->flush();

        $route = $request->headers->get('referer');

        return $this->redirect($route);
    }

    #[Route('/api/expense/add', name: 'api_expense_add')]
    public function apiAdd(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $parameters = json_decode($request->getContent(), true);
        $categoryId = (int) $parameters['categoryId'];
        $userId = (int) $parameters['userId'];

        try {
            if (!$categoryId) {
                throw new \Exception('Empty category id');
            }

            $category = $entityManager->getRepository(Category::class)->findOneBy(['id' => $categoryId]);

            if (!$category || $category->getType() !== $parameters['type'] || ($category->getUserId() !== $userId)) {
                throw new \Exception('Wrong category selected, please verify the correctness of your expense');
            }

            $expense = new Expense();

            $expense->setUserId($userId);
            $expense->setType($parameters['type']);
            $expense->setCategoryId($categoryId);
            $expense->setSpending($parameters['spending']);
            $expense->setDescription($parameters['description']);
            $expense->setDate($parameters['date']);

            $errors = $validator->validate($expense);
            if (count($errors) > 0) {
                throw new \Exception((string) $errors);
            }

            $entityManager->persist($expense);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }

        return new JsonResponse('Success', 200);
    }
}
