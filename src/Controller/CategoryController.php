<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/categories-by-type', name: 'categories_by_type', methods: ['post'])]
    public function categoriesByType(
        ManagerRegistry $doctrine,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $body = json_decode($request->getContent());

        $categories = $doctrine->getRepository(Category::class)->findBy(
            ['type' => $body->type, 'userId' => $body->userId]
        );

        return $this->json(array_map(fn($cat) => $cat->getName(), $categories));
    }

    #[Route('/categories', name: 'categories_index')]
    public function list(
        ManagerRegistry $doctrine,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $userId = $user->getId();

        $major = array_map(
            fn($cat) => ['name' => $cat->getName(), 'type' => $cat->getType(), 'description' => $cat->getDescription()],
            $doctrine->getRepository(Category::class)->findBy(['type' => 'major', 'userId' => $userId])
        );
        $home = array_map(
            fn($cat) => ['name' => $cat->getName(), 'type' => $cat->getType(), 'description' => $cat->getDescription()],
            $doctrine->getRepository(Category::class)->findBy(['type' => 'home', 'userId' => $userId])
        );
        $daily = array_map(
            fn($cat) => ['name' => $cat->getName(), 'type' => $cat->getType(), 'description' => $cat->getDescription()],
            $doctrine->getRepository(Category::class)->findBy(['type' => 'daily', 'userId' => $userId])
        );

        $category = new Category();
        $this->denyAccessUnlessGranted('ROLE_USER');
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $category->setUserId($userId);

            $entityManager->persist($category);
            $entityManager->flush();
        }

        return $this->render(
            'category/list.html.twig',
            ['form' => $form, 'major' => $major, 'daily' => $daily, 'home' => $home]
        );
    }
}