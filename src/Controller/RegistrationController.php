<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        ManagerRegistry $doctrine,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $duplicateUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);

            if ($duplicateUser) {
                return $this->render('registration/register.html.twig', [
                    'error' => 'Such username already exists, please choose another.',
                    'registrationForm' => $form->createView(),
                ]);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            foreach (CategoryRepository::getDefaultCategories() as $type => $categories) {
                foreach ($categories as $category) {
                    $cat = new Category();
                    $cat
                        ->setType($type)
                        ->setDescription($category['description'] ?? '')
                        ->setName($category['name'] ?? '')
                        ->setUserId($user->getId());

                    $entityManager->persist($cat);
                    $entityManager->flush();
                }
            }

            return $this->redirectToRoute('expense_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'error' => ''
        ]);
    }
}
