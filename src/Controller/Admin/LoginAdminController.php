<?php

namespace App\Controller\Admin;


use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class LoginAdminController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/admin/login', name: 'admin_login', methods: 'POST')]
     public function loginAdmin(Request $request, JWTTokenManagerInterface $tokenManager): JsonResponse
     {
         $data = $request->toArray();
         $email = $data['email'];
         $password = $data['password'];

         $user = $this->userRepository->findOneBy(['email' => $email]);

         if (!$user) {
             return $this->json(['message' => 'User Not Found']);
         }

         if (!in_array('ROLE_ADMIN', $user->getRoles())) {
             return $this->json(['message' => 'No Admin']);
         }

         $isValid = $this->passwordHasher->isPasswordValid($user, $password);

         if (!$isValid) {
             return $this->json(['message' => 'invalid password']);
         }

         $token = $tokenManager->create($user);

         return $this->json(['token' => $token]);
     }

    #[Route('/user/login', name: 'user_login', methods: 'POST')]
    public function login(Request $request, JWTTokenManagerInterface $tokenManager): JsonResponse
    {
        $data = $request->toArray();
        $email = $data['email'];
        $password = $data['password'];

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['message' => 'User Not Found']);
        }

        $isValid = $this->passwordHasher->isPasswordValid($user, $password);

        if (!$isValid) {
            return $this->json(['message' => 'invalid password']);
        }

        $token = $tokenManager->create($user);

        return $this->json(['token' => $token]);
    }
}