<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    /* Ajouter un nouvel article */

    #[Route('/api/article/new', name: 'app_api_article_new', methods: ['POST'])]
    public function createPost(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $body = $request->getContent();
        $article = $serializer->deserialize($body, Article::class, 'json');
        $user = $this->getUser();

        $article->setAuteur($user);

        $errors = $validator->validate($article);
        if(count($errors) >0 ){
            return $this->json($errors, 400);
        }

        $em->persist($article);
        $em->flush();

        return $this->json([
            'status' => 201,
            'message' => 'la ressource a été créée',
            'data' => $article
        ], 201, [], ['groups' => 'article']);
    }

    /* Afficher tous les articles */

    #[Route('/api/articles', name: 'app_api_articles')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();
        return $this->json([
            'status' => 200,
            'message' => 'la ressource a été trouvée',
            'data' => $articles
        ], 200, [], ['groups' => 'article']);
    }

    /* Afficher un article par son id */

    #[Route('/api/article/show/{id}', name: 'app_api_article_show')]
    public function showUser(ArticleRepository $articleRepository, $id): Response
    {
        $user = $articleRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'Article non trouvé'], 404);
        }

        return $this->json([
            'status' => 200,
            'message' => 'La ressource a été trouvée',
            'data' => $user
        ], 200, [], ['groups' => 'article']);
    }

    /* Mettre à jour un article */

    #[Route('/api/article/update/{id}', name: 'app_api_article_update', methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, $id): Response
    {
        $body = $request->getContent();
        $updatedArticle = $serializer->deserialize($body, Article::class, 'json');
        
        // Récupérer l'utilisateur à mettre à jour
        $article = $em->getRepository(Article::class)->find($id);
        
        if (!$article) {
            return $this->json(['message' => 'Article non trouvé'], 404);
        }
        
        // Mettre à jour les propriétés de l'utilisateur
        $article->setTitre($updatedArticle->getTitre());
        $article->setContenu($updatedArticle->getContenu());
        // Ajoutez d'autres setters pour mettre à jour d'autres propriétés de l'utilisateur si nécessaire
        
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        
        $em->flush();
        
        return $this->json([
            'status' => 200,
            'message' => 'L\'article a été mise à jour',
            'data' => $article
        ], 200, [], ['groups' => 'article']);
    }

    /* Supprimer un article */

    #[Route('/api/article/delete/{id}', name: 'app_api_article_delete', methods:['DELETE'])]
    public function deleteUser(EntityManagerInterface $em, $id): Response
    {
        // Récupérer l'utilisateur à supprimer
        $article = $em->getRepository(Article::class)->find($id);
        
        if (!$article) {
            return $this->json(['message' => 'Article non trouvé'], 404);
        }

        $em->remove($article);
        $em->flush();

        return $this->json([
            'status' => 200,
            'message' => 'L\'article a été supprimée'
        ], 200);
    }

}
