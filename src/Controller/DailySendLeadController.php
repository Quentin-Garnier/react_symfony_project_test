<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class DailySendLeadController extends AbstractController
{
    #[Route('/daily_send_lead', name: 'app_daily_send_lead', methods: ['GET'])]
    public function index(EntityManagerInterface $db): Response
    {
        // Exécuter la requête SQL pour récupérer les IDs distincts des annonceurs
        $connection = $db->getConnection();
        $query = 'SELECT COUNT(*), id_annonceur 
              FROM table_collecte 
              WHERE DATE(date_add) = CURDATE()-1
              GROUP BY id_annonceur 
              ORDER BY COUNT(*) DESC';
        $stmt = $connection->prepare($query);
        $resultSet = $stmt->executeQuery();

        // Récupérer les résultats
        $result = $resultSet->fetchAllAssociative();

        
        $results = [];
        foreach ($result as $row) {
            $idAnnonceur = $row['id_annonceur'];

            // Exécuter la requête pour obtenir tous les éléments pour chaque annonceur
            $detailsQuery = 'SELECT * 
                            FROM table_collecte 
                            WHERE id_annonceur = :id_annonceur 
                            AND DATE(date_add) = CURDATE()-1';
            $detailsStmt = $connection->prepare($detailsQuery);
            $detailsStmt->bindValue(':id_annonceur', $idAnnonceur);
            $detailsResult = $detailsStmt->executeQuery();

            // Récupérer tous les détails
            $details = $detailsResult->fetchAllAssociative();

            // Ajouter les résultats pour cet annonceur
            $results[] = [
                'id_annonceur' => $idAnnonceur,
                'details' => $details,
            ];
        }



        // Retourner les IDs en JSON
        return $this->json([
            'distinct_annonceurs' => $results,
        ]);
    }
}
