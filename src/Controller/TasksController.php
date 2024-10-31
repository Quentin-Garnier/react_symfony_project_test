<?php

namespace App\Controller;

use App\Controller\TokenAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

Class TasksController extends AbstractController
{
    private $tokenAuthenticator;

    public function __construct(TokenAuthenticator $tokenAuthenticator)
    {
        $this->tokenAuthenticator = $tokenAuthenticator;
    }
    
    #[Route('/tasks', name: 'get_tasks', methods: ['GET'])]
    public function getTasks(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->tokenAuthenticator->validateToken($request);

        // Récupérer les tâches de l'utilisateur
        $tasks = $entityManager->getConnection()->fetchAllAssociative('SELECT * FROM tasks');

        return new JsonResponse($tasks, 200);
    }


    #[Route('/tasks', name: 'inject_task', methods: ['POST'])]
    public function postTask(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->tokenAuthenticator->validateToken($request);

        // Récupérer les données du body de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que les champs requis sont bien présents
        $requiredFields = ['nom', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['error' => 'Missing parameter: ' . $field], 400);
            }
        }

        // Insérer les données dans la base de données
        try {
            $entityManager->getConnection()->insert('tasks', [
                'nom' => $data['nom'],
                'description' => $data['description'],
            ]);

            return new JsonResponse(['success' => 'Task successfully inserted'], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to insert task: ' . $e->getMessage()], 500);
        }
    }




    #[Route('/tasks/{id}', name: 'update_task', methods: ['PUT'])]
    public function updateTask(Request $request, EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $this->tokenAuthenticator->validateToken($request);

        // Récupérer les données du body de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que la tâche existe
        $task = $entityManager->getConnection()->fetchAssociative('SELECT * FROM tasks WHERE task_id = ?', [$id]);
        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        // Mettre à jour la tâche
        try {
            $entityManager->getConnection()->update('tasks', $data, ['task_id' => $id]);

            return new JsonResponse(['success' => 'Task successfully updated'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to update task: ' . $e->getMessage()], 500);
        }
    }





    #[Route('/tasks/{id}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(Request $request, EntityManagerInterface $entityManager, $id): JsonResponse
    {
        // Validation du token
        $this->tokenAuthenticator->validateToken($request);

        // Vérifier que la tâche existe
        $task = $entityManager->getConnection()->fetchAssociative('SELECT * FROM tasks WHERE task_id = ?', [$id]);
        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        // Supprimer la tâche
        try {
            // Retirer l'ID de la tâche de la colonne assigned_tasks des utilisateurs
            $entityManager->getConnection()->executeStatement(
                'UPDATE users 
                SET assigned_tasks = (
                    SELECT JSON_ARRAYAGG(value) 
                    FROM JSON_TABLE(assigned_tasks, "$[*]" COLUMNS(value INT PATH "$")) AS jt
                    WHERE value != ?)
                WHERE JSON_CONTAINS(assigned_tasks, ?)',
                [$id, $id]
            );

            // Maintenant, supprimer la tâche
            $entityManager->getConnection()->delete('tasks', ['task_id' => $id]);

            return new JsonResponse(['success' => 'Task successfully deleted'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to delete task: ' . $e->getMessage()], 500);
        }
    }




    #[Route('/tasks/{task_id}/assign', name: 'assign_task', methods: ['PUT'])]
    public function assignTask(Request $request, EntityManagerInterface $entityManager, $task_id): JsonResponse
    {
        $this->tokenAuthenticator->validateToken($request);

        // Récupérer les données du body de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que le champ requis est bien présent
        if (!isset($data['user_id'])) {
            return new JsonResponse(['error' => 'Missing parameter: user_id'], 400);
        }

        // Vérifier que la tâche existe avec l'identifiant task_id
        $task = $entityManager->getConnection()->fetchAssociative('SELECT * FROM tasks WHERE task_id = ?', [$task_id]);
        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        // Vérifier que l'utilisateur existe avec l'identifiant user_id
        $user = $entityManager->getConnection()->fetchAssociative('SELECT * FROM users WHERE user_id = ?', [$data['user_id']]);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Assigner la tâche à l'utilisateur
        try {
            // Mettre à jour la colonne assigned_tasks de l'utilisateur
            $assignedTasks = json_decode($user['assigned_tasks'], true) ?? [];
            if (!in_array($task_id, $assignedTasks)) {
                $assignedTasks[] = (int)$task_id; // Assurez-vous que c'est un entier
                $entityManager->getConnection()->update('users', ['assigned_tasks' => json_encode($assignedTasks)], ['user_id' => $data['user_id']]);
            }

            // Mettre à jour la colonne assigned_users de la tâche
            $assignedUsers = json_decode($task['assigned_users'], true) ?? [];
            if (!in_array($data['user_id'], $assignedUsers)) {
                $assignedUsers[] = (int)$data['user_id']; // Assurez-vous que c'est un entier
                $entityManager->getConnection()->update('tasks', ['assigned_users' => json_encode($assignedUsers)], ['task_id' => $task_id]);
            }

            return new JsonResponse(['success' => 'Task successfully assigned'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to assign task: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/response', name: 'response', methods: ['POST'])]
    public function response(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->tokenAuthenticator->validateToken($request);

        // Récupérer les données du body de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que les champs requis sont bien présents
        $requiredFields = ['nom', 'description', 'assigned_task', 'responding_user'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['error' => 'Missing parameter: ' . $field], 400);
            }
        }

        // Insérer les données dans la base de données
        try {
            $entityManager->getConnection()->insert('responses', [
                'nom' => $data['nom'],
                'description' => $data['description'],
                'assigned_task' => $data['assigned_task'],
                'responding_user' => $data['responding_user'],
            ]);

            return new JsonResponse(['success' => 'Response successfully inserted'], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to insert response: ' . $e->getMessage()], 500);
        }
    }
    
}

?>