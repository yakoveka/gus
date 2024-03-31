<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
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
    ): JsonResponse {
        $body = json_decode($request->getContent());

        $categories = $doctrine->getRepository(Category::class)->findBy(
            ['type' => $body->type, 'userId' => $body->userId]
        );

        return $this->json(array_map(fn($cat) => ['name' => $cat->getName(), 'id' => $cat->getId()], $categories));
    }

    #[Route('/categories', name: 'categories_index')]
    public function list(
        ManagerRegistry $doctrine,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $userId = $user->getId();

        $major = CategoryRepository::prepareCategoriesByType('major', $doctrine, $userId);
        $home = CategoryRepository::prepareCategoriesByType('home', $doctrine, $userId);
        $daily = CategoryRepository::prepareCategoriesByType('daily', $doctrine, $userId);

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
