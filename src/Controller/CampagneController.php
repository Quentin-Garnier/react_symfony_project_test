<?php

namespace App\Controller;

use App\Entity\DataCampagne;
use App\Entity\DataCampagneUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CampagneController extends AbstractController
{
    #[Route('/api/campagne/create', name: 'create_campagne', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Récupération et décodage des données JSON
        $data = json_decode($request->getContent(), true);

        // Validation des données obligatoires
        if (!isset($data['campagne']['campagne'], $data['campagne']['title'], $data['campagne']['id_annonceur'], $data['campagne']['form'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        // Nouvelle campagne à ajouter
        $newCampagne = [
            'campagne' => $data['campagne']['campagne'],
            'title' => $data['campagne']['title'],
            'id_annonceur' => $data['campagne']['id_annonceur'],
            'active' => $data['campagne']['active'] ?? true,
            'form' => $data['campagne']['form'],
            'postback' => $data['campagne']['postback'] ?? '',
            'url_postback' => $data['campagne']['url_postback'] ?? '',
            'no_ws' => $data['campagne']['no_ws'] ?? false,
        ];

        if ($data['campagne']['no_ws'] === false) {
            $newCampagne['data_webservice'] = [
                'ws_model' => $data['campagne']['data_webservice']['ws_model'],
                'ws' => $data['campagne']['data_webservice']['ws'],
            ];

            // Générer le fichier API
            // $this->generateApiFile($newCampagne['data_webservice']['ws_model']);
        }


        // Ajout de la campagne via DataCampagne
        DataCampagne::addCampagne($newCampagne);

        // Définir le chemin vers le fichier DataCampagne.php
        $filePath = __DIR__ . '/../Entity/DataCampagne.php';
        
        DataCampagneUpdater::updateCampagnesVariable($filePath, DataCampagne::getAllCampagnes());



        // Traitement des formulaires
        if (isset($data['form']) && is_array($data['form'])) {
            $newForm = [];
            foreach ($data['form'] as $field) {
                if (isset($field['field'], $field['type'])) {
                    $newForm[] = array(
                        'name' => $field['field'],
                        'type' => $field['type']
                    );
                }
            }

            // return new JsonResponse([
            //     $newForm
            // ]);

            // Mise à jour de la variable $forms dans DataCampagne
            DataCampagne::addForm($data);

            // Mise à jour des forms dans le fichier DataCampagne.php
            DataCampagneUpdater::updateFormsVariable($filePath, DataCampagne::getAllForms());
        }


        // Générer la vue du formulaire
        // $this->generateFormView($data['campagne']['form'], $data['form']);

        // Retourner les campagnes mises à jour
        return new JsonResponse([
            'message' => 'Campagne added successfully',
            'campagnes' => DataCampagne::getAllCampagnes(),
            'forms' => DataCampagne::getAllForms()
        ]);
    }








    private function generateApiFile(string $modelName): void
    {
        // Correctement définir le chemin vers le répertoire Webservices
        $directoryPath = __DIR__ . "/../Webservices"; // Répertoire où les fichiers seront générés

        // Vérifier si le répertoire existe, sinon le créer
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true); // Créer le répertoire avec les permissions appropriées
        }

        // Définir le chemin complet du fichier
        $filePath = $directoryPath . "/{$modelName}.php";

        // Générer un fichier avec une classe simple, sans dépendance à CodeIgniter
        if (!file_exists($filePath)) {
            $content = "<?php\n\nclass {$modelName} {\n\n    public function prepareLead(\$user = false)\n    {\n        // Logique de préparation de lead ici\n    }\n\n}";
            file_put_contents($filePath, $content);
        }
    }




    private function generateFormView(string $formName, array $formFields): void
    {
        // Chemin vers le template du formulaire
        $templatePath = __DIR__ . "/../../templates/{$formName}.html.twig";

        // Vérifier si le template existe déjà
        if (!file_exists($templatePath)) {
            // Générer un formulaire dynamique avec Twig
            $content = "{% extends 'base.html.twig' %}\n\n{% block body %}\n<form method=\"POST\" action=\"\">\n";
            
            // Parcourir les champs du formulaire et les ajouter dynamiquement
            foreach ($formFields as $field) {
                $content .= "<div class=\"form-group\">\n";
                $content .= "<label for=\"{$field['field']}\">{$field['field']}</label>\n";
                
                // Gérer le type de champ, par défaut, un champ texte
                $inputType = $field['type'] ?? 'text'; // Assurez-vous que 'type' existe dans la structure de champ
                $content .= "<input type=\"{$inputType}\" id=\"{$field['field']}\" name=\"{$field['field']}\" class=\"form-control\" />\n";
                $content .= "</div>\n";
            }
            
            $content .= "<button type=\"submit\" class=\"btn btn-primary\">Submit</button>\n</form>\n{% endblock %}";
            file_put_contents($templatePath, $content);
        }
    }
}
