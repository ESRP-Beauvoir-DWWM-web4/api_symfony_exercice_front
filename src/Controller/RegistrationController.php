<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'app_api_users_register', methods: ['POST'])]
    public function register(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $body = $request->getContent();
        $userData = $serializer->deserialize($body, User::class, 'json');

        $hashedPassword = $userPasswordHasher->hashPassword($userData, $userData->getPassword());
        $userData->setPassword($hashedPassword);

        $em->persist($userData);
        $em->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Utilisateur inscrit avec succès',
            'data' => $userData
        ], 201, [], ['groups' => 'user']);
    }
}
