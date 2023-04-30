<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class RegistrationController extends AbstractController
{
    #[Route('/user/registration', name: 'user_registration', methods:['POST'])]
    public function index(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = $request->toArray();
        $email = $data['email'];
        $password = $data['password'];
  
        $user = new User();
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setUsername($email);
        $entityManager->persist($user);
        $entityManager->flush();
  
        return $this->json(['message' => 'Registered Successfully']);
    }
}
