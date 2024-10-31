<?php

namespace App\Controller;

use App\Controller\TokenAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

Class ResponseController extends AbstractController
{
    private $tokenAuthenticator;

    public function __construct(TokenAuthenticator $tokenAuthenticator)
    {
        $this->tokenAuthenticator = $tokenAuthenticator;
    }


    #[Route('/responses/{id}', name: 'recup_response_user', methods: ['GET'])]
    public function recupUserResponses(Request $request, EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $this->tokenAuthenticator->validateToken($request);

        // Exécuter la requête pour récupérer toutes les entrées de la table 'users'
        try {
            $responses = $entityManager->getConnection()->fetchAllAssociative('SELECT * FROM responses WHERE responding_user = ?', [$id]);

            return new JsonResponse($responses, 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to retrieve users: ' . $e->getMessage()], 500);
        }
    }

}


?>