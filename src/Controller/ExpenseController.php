<?php

namespace App\Controller;

use App\Form\ExpenseType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            'category' => 'Test',
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

        $major = CategoryRepository::prepareCategories($doctrine, 'major', $userId);
        $home = CategoryRepository::prepareCategories($doctrine, 'home', $userId);
        $daily = CategoryRepository::prepareCategories($doctrine, 'daily', $userId);

        $expense = new Expense();
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(ExpenseType::class, $expense);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $expense = $form->getData();
            $expense->setUserId($userId);

            $entityManager->persist($expense);
            $entityManager->flush();
        }

        return $this->render(
            'expense/list.html.twig',
            ['form' => $form, 'daily' => $daily, 'major' => $major, 'home' => $home, 'userId' => $userId]
        );
    }

//    #[Route('/expenses', name: 'expense_create', methods: ['post'])]
//    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
//    {
//        $entityManager = $doctrine->getManager();
//
//        $parameters = json_decode($request->getContent(), true);
//
//        $expense = new Expense();
//        $expense->setCategory($parameters['category'] ?? '');
//        $expense->setDescription($parameters['description'] ?? '');
//        $expense->setSpending((float)$parameters['spending'] ?? 0);
//        $expense->setCurrency($parameters['currency'] ?? '');
//        $expense->setDate($parameters['date'] ?? '');
//
//        $entityManager->persist($expense);
//        $entityManager->flush();
//
//        $data = [
//            'id' => $expense->getId(),
//            'category' => $expense->getCategory(),
//            'description' => $expense->getDescription(),
//            'spending' => $expense->getSpending(),
//            'currency' => $expense->getCurrency(),
//            'date' => $expense->getDate(),
//        ];
//
//        return $this->json($data);
//    }


//    #[Route('/expenses/{id}', name: 'expense_show', methods: ['get'])]
//    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
//    {
//        $expense = $doctrine->getRepository(Expense::class)->find($id);
//
//        if (!$expense) {
//            return $this->json('No expense found for id ' . $id, 404);
//        }
//
//        $data = [
//            'id' => $expense->getId(),
//            'category' => $expense->getCategory(),
//            'description' => $expense->getDescription(),
//            'spending' => $expense->getSpending(),
//            'currency' => $expense->getCurrency(),
//        ];
//
//        return $this->json($data);
//    }

//    #[Route('/expenses/{id}', name: 'expense_update', methods: ['put', 'patch'])]
//    public function update(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
//    {
//        $entityManager = $doctrine->getManager();
//        $expense = $entityManager->getRepository(Expense::class)->find($id);
//
//        if (!$expense) {
//            return $this->json('No expense found for id' . $id, 404);
//        }
//
//        $parameters = json_decode($request->getContent(), true);
//
//        $expense->setCategory($parameters['category'] ?? '');
//        $expense->setDescription($parameters['description'] ?? '');
//        $expense->setSpending((float)$parameters['spending'] ?? 0);
//        $expense->setCurrency($parameters['currency'] ?? '');
//        $expense->setDate($parameters['date']);
//        $entityManager->flush();
//
//        $data = [
//            'id' => $expense->getId(),
//            'category' => $expense->getCategory(),
//            'description' => $expense->getDescription(),
//            'spending' => $expense->getSpending(),
//            'currency' => $expense->getCurrency(),
//            'date' => $expense->getDate(),
//        ];
//
//        return $this->json($data);
//    }

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
