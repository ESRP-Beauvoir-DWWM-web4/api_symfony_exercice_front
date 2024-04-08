<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiUserController extends AbstractController
{
    /* Afficher de tous les utilisateurs */

    #[Route('/api/users', name: 'app_api_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->json([
            'status' => 200,
            'message' => 'la ressource a été trouvée',
            'data' => $users
        ], 200, [], ['groups' => 'user']);
    }

    /* Afficher un utilisateur par son ID */

    #[Route('/api/user/show/{id}', name: 'app_api_user_show')]
    public function showUser(UserRepository $userRepository, $id): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return $this->json([
            'status' => 200,
            'message' => 'La ressource a été trouvée',
            'data' => $user
        ], 200, [], ['groups' => 'user']);
    }

    /* Mettre à jour un utilisateur */

    #[Route('/api/user/update/{id}', name: 'app_api_user_update', methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator,UserPasswordHasherInterface $passwordHasher, $id): Response
    {
        $body = $request->getContent();
        $updatedUser = $serializer->deserialize($body, User::class, 'json');
        
        // Récupérer l'utilisateur à mettre à jour
        $user = $em->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        
        // Mettre à jour les propriétés de l'utilisateur
        $user->setEmail($updatedUser->getEmail());
        $newPassword = $updatedUser->getPassword();
        if ($newPassword !== null) {
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
        }
        // Ajoutez d'autres setters pour mettre à jour d'autres propriétés de l'utilisateur si nécessaire
        
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        
        $em->flush();
        
        return $this->json([
            'status' => 200,
            'message' => 'La ressource a été mise à jour',
            'data' => $user
        ], 200, [], ['groups' => 'user']);
    }

    /* Supprimer un utilisateur */

    #[Route('/api/user/delete/{id}', name: 'app_api_user_delete', methods:['DELETE'])]
    public function deleteUser(EntityManagerInterface $em, $id): Response
    {
        // Récupérer l'utilisateur à supprimer
        $user = $em->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'status' => 200,
            'message' => 'La ressource a été supprimée'
        ], 200);
    }
}
