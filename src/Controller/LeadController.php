<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LeadController extends AbstractController
{
    #[Route('/injectLead', name: 'inject_lead', methods: ['POST'])]
    public function injectLead(Request $request, Connection $connection): JsonResponse
    {
        // Récupérer les données du body de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que les champs requis sont bien présents
        $requiredFields = ['nom', 'prenom', 'id', 'id_leadflow', 'email', 'telephone', 'date_transmission', 'retour_LF'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['error' => 'Missing parameter: ' . $field], 400);
            }
        }

        // Insérer les données dans la base de données
        try {
            $connection->insert('test', [
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'id' => $data['id'],
                'id_leadflow' => $data['id_leadflow'],
                'email' => $data['email'],
                'telephone' => $data['telephone'],
                'date_transmission' => $data['date_transmission'],
                'retour_LF' => $data['retour_LF'],
            ]);

            return new JsonResponse(['success' => 'Lead successfully inserted'], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to insert lead: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/recupLead', name: 'recup_lead', methods: ['GET'])]
    public function recupLead(Connection $connection): JsonResponse
    {
        // Exécuter la requête pour récupérer toutes les entrées de la table 'test'
        try {
            $leads = $connection->fetchAllAssociative('SELECT * FROM test');

            return new JsonResponse($leads, 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to retrieve leads: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/updateLead/{id}', name: 'update_lead', methods: ['PUT'])]
    public function updateLead(Request $request, Connection $connection, int $id): JsonResponse
    {

        // Vérifier si le lead avec cet ID existe
        $lead = $connection->fetchAssociative('SELECT * FROM test WHERE id_leadflow = ?', [$id]);

        if (!$lead) {
            return new JsonResponse(['error' => 'Lead not found'], 404);
        }

        // Mettre à jour le champ 'retour_LF' du lead
        try {
            $connection->update('test', [
                'retour_LF' => "OK",
            ], ['id_leadflow' => $id]);

            return new JsonResponse(['success' => 'Lead retour_LF successfully updated'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to update lead: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/pushLead/{id}', name: 'push_lead', methods: ['POST'])]
    public function pushLead(Request $request, int $id): JsonResponse
    {
        // Récupérer le lead depuis la base de données
        $lead = $connection->fetchAssociative('SELECT * FROM test WHERE id_leadflow = ?', [$id]);

        if (!$lead) {
            return new JsonResponse(['error' => 'Lead not found'], 404);
        }

        // Préparer les informations à envoyer
        $leadInfo = [
            'id_leadflow' => $lead['id_leadflow'],
            'source' => '17', // Ajoute la source comme spécifié
            // Ajoute d'autres champs nécessaires ici, par exemple :
            'nom' => $lead['nom'],
            'id' => $lead['id'],
            'prenom' => $lead['prenom'],
            'email' => $lead['email'],
            'telephone' => $lead['telephone'],
            'date_transmission' => $lead['date_transmission'],
            'retour_LF' => $lead['retour_LF'],
        ];

        // Envoyer les données à l'URL externe
        $url = 'https://leadflow.cardata.fr';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($leadInfo));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Vérifier si la requête a réussi
        if ($httpCode !== 200) {
            return new JsonResponse(['error' => 'Failed to send lead info', 'httpCode' => $httpCode], $httpCode);
        }

        return new JsonResponse(['success' => 'Lead info sent successfully'], 200);
    }
}
