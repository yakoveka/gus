<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Expense;

class ExpenseController extends AbstractController
{
    #[Route('/', name: 'back')]
    public function redirectToDashboard(): JsonResponse
    {
        return $this->json([
            'id' => 1,
            'category' => 'Test',
        ]);
    }

    #[Route('/expenses', name: 'expense_index', methods: ['get'])]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $expenses = $doctrine
            ->getRepository(Expense::class)
            ->findAll();

        $data = [];

        foreach ($expenses as $expense) {
            $data[] = [
                'id' => $expense->getId(),
                'category' => $expense->getCategory(),
                'description' => $expense->getDescription(),
                'spending' => $expense->getSpending(),
                'currency' => $expense->getCurrency(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/expenses', name: 'expense_create', methods: ['post'])]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $expense = new Expense();
        $expense->setCategory($request->request->get('name'));
        $expense->setDescription($request->request->get('description'));
        $expense->setSpending($request->request->get('spending'));
        $expense->setCurrency($request->request->get('currency'));

        $entityManager->persist($expense);
        $entityManager->flush();

        $data = [
            'id' => $expense->getId(),
            'category' => $expense->getCategory(),
            'description' => $expense->getDescription(),
            'spending' => $expense->getSpending(),
            'currency' => $expense->getCurrency(),
        ];

        return $this->json($data);
    }


    #[Route('/expenses/{id}', name: 'expense_show', methods: ['get'])]
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $expense = $doctrine->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->json('No expense found for id ' . $id, 404);
        }

        $data = [
            'id' => $expense->getId(),
            'category' => $expense->getCategory(),
            'description' => $expense->getDescription(),
            'spending' => $expense->getSpending(),
            'currency' => $expense->getCurrency(),
        ];

        return $this->json($data);
    }

    #[Route('/expenses/{id}', name: 'expense_update', methods: ['put', 'patch'])]
    public function update(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $expense = $entityManager->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->json('No expense found for id' . $id, 404);
        }

        $expense->setCategory($request->request->get('name'));
        $expense->setDescription($request->request->get('description'));
        $expense->setSpending($request->request->get('spending'));
        $expense->setCurrency($request->request->get('currency'));
        $entityManager->flush();

        $data = [
            'id' => $expense->getId(),
            'category' => $expense->getCategory(),
            'description' => $expense->getDescription(),
            'spending' => $expense->getSpending(),
            'currency' => $expense->getCurrency(),
        ];

        return $this->json($data);
    }

    #[Route('/expenses/{id}', name: 'expense_delete', methods: ['delete'])]
    public function delete(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $expense = $entityManager->getRepository(Expense::class)->find($id);

        if (!$expense) {
            return $this->json('No expense found for id' . $id, 404);
        }

        $entityManager->remove($expense);
        $entityManager->flush();

        return $this->json('Deleted a expense successfully with id ' . $id);
    }
}
