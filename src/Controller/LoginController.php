<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.'
        );
    }

    #[Route(path: '/api/login', name: 'api_login')]
    public function loginApi(
        Request $request,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $userPasswordHasher
    ): JsonResponse {
        $parameters = json_decode($request->getContent(), true);

        $user = $doctrine->getRepository(User::class)->findOneBy(['username' => $parameters['username']]);

        if (!$user) {
            return $this->json('Invalid credentials for user ' . $parameters['username'], 401);
        }

        return $userPasswordHasher->isPasswordValid(
            $user,
            $parameters['password']
        ) ? $this->json($user->getId()) : $this->json('Invalid credentials for user ' . $parameters['username'], 401);
    }
}
