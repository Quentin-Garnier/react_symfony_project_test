<?php

namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException; 

class UsersController extends AbstractController
{
    private $jwtSecret; // Ajoutez ceci

    public function __construct()
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        
        // Assurez-vous que la clé secrète est correctement chargée
        if (empty($this->jwtSecret)) {
            throw new \Exception('JWT_SECRET is not set in the environment variables.');
        }
    }




    #[Route('/inscription', name: 'inject_user', methods: ['POST'])]
    public function injectUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données du body de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que les champs requis sont bien présents
        $requiredFields = ['nom', 'prenom', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['error' => 'Missing parameter: ' . $field], 400);
            }
        }

        // Insérer les données dans la base de données
        try {
            $entityManager->getConnection()->insert('users', [
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $userId = $entityManager->getConnection()->lastInsertId();

            if (empty($this->jwtSecret) || !is_string($this->jwtSecret)) {
                return new JsonResponse(['error' => 'JWT secret is not defined correctly'], 500);
            }
            
            // Générer le token JWT
            $payload = [
                'user_id' => $userId,
                'exp' => time() + 3600 // Token valide pour 1 heure
            ];

            $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

            return new JsonResponse(['success' => 'User successfully inserted', "token" => $token, "user" => [
                'user_id' => $userId,
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
            ]], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to insert user: ' . $e->getMessage()], 500);
        }
    }



    #[Route('/connection', name: 'connection', methods: ['POST'])]
    public function connection(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données du body de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que les champs requis sont bien présents
        $requiredFields = ['email', 'password'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['error' => 'Missing parameter: ' . $field], 400);
            }
        }

        // Insérer les données dans la base de données
        try {
            $user = $entityManager->getConnection()->fetchAssociative('SELECT * FROM users WHERE email = ? AND password = ?', [$data['email'], $data['password']]);

            if ($user && $data['password'] === $user['password']) { // Remplacez ceci par une vérification sécurisée si vous avez hashé le mot de passe
                if (empty($this->jwtSecret) || !is_string($this->jwtSecret)) {
                    return new JsonResponse(['error' => 'JWT secret is not defined correctly'], 500);
                }
                
                // Générer le token JWT
                $payload = [
                    'user_id' => $user['user_id'],
                    'exp' => time() + 3600 // Token valide pour 1 heure
                ];

                $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

                return new JsonResponse(['success' => 'User successfully connected', 'token' => $token, "user" => $user], 200);

            } else {

                return new JsonResponse(['error' => 'Failed to connect user: user not found'], 404);
            }

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to connect user: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/users', name: 'recup_user', methods: ['GET'])]
    public function recupUser(EntityManagerInterface $entityManager): JsonResponse
    {
        // Exécuter la requête pour récupérer toutes les entrées de la table 'users'
        try {
            $users = $entityManager->getConnection()->fetchAllAssociative('SELECT * FROM users');

            return new JsonResponse($users, 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to retrieve users: ' . $e->getMessage()], 500);
        }
    }
}

?>