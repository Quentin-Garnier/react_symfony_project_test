<?php

namespace App\Controller;

use App\Entity\DataCampagne;
use App\Entity\DataCampagneUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

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
            $this->generateApiFile($newCampagne['data_webservice']['ws_model']);
        }


        // Ajout de la campagne via DataCampagne
        DataCampagne::addCampagne($newCampagne);

        // Définir le chemin vers le fichier DataCampagne.php
        $filePath = __DIR__ . '/../Entity/DataCampagne.php';
        
        DataCampagneUpdater::updateCampagnesVariable($filePath, DataCampagne::getAllCampagnes());



        // Traitement des formulaires
        if (isset($data['form']) && is_array($data['form'])) {
            // $newForm = [];
            // foreach ($data['form'] as $field) {
            //     if (isset($field['field'], $field['type'])) {
            //         $newForm[] = array(
            //             'name' => $field['field'],
            //             'type' => $field['type']
            //         );
            //     }
            // }

            // return new JsonResponse([
            //     $newForm
            // ]);

            // Mise à jour de la variable $forms dans DataCampagne
            DataCampagne::addForm($data);

            // Mise à jour des forms dans le fichier DataCampagne.php
            DataCampagneUpdater::updateFormsVariable($filePath, DataCampagne::getAllForms());
        }


        // Générer la vue du formulaire
        $this->generateView($data['campagne']['campagne'], $data['form'], $data['campagne']['title']);

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




    private function generateView(string $formName, array $formFields, string $campagneTitle): void
    {
        // Créer le dossier pour la campagne si il n'existe pas
        $campaignDir = __DIR__ . "/../../templates/{$formName}";
        if (!file_exists($campaignDir)) {
            mkdir($campaignDir, 0777, true);
        }

        // Tableau pour les chemins des fichiers à générer
        $templates = [
            'landing' => "{$campaignDir}/landing.html.twig",
            'recap' => "{$campaignDir}/recap.html.twig",
        ];

        // Contenus pour chaque fichier Twig
    $contents = [
        'landing' => "{% block body %}\n<h2>Formulaire pour {{ campagneTitle }}</h2>\n"
            . "<form action=\"/view/{{slug}}/recap\" method=\"POST\">\n"
            . "{{ formHtml|raw }}\n"
            . "<button type=\"submit\" class=\"btn btn-primary\">Envoyer</button>\n"
            . "</form>\n"
            . "{% endblock %}",
        
        'recap' => "{% block body %}\n<h2>Récapitulatif pour {{ campagneTitle }}</h2>\n"
            . "<ul>\n"
            . "{% for field, value in formData %}\n"
            . "<li><strong>{{ field|capitalize }}:</strong> {{ value }}</li>\n"
            . "{% endfor %}\n"
            . "</ul>\n"
            . "{% endblock %}",
    ];

        // Générer les fichiers Twig si nécessaire
        foreach ($templates as $key => $templatePath) {
            // Vérifier si le fichier existe déjà
            if (!file_exists($templatePath)) {
                // Créer le fichier avec le contenu
                file_put_contents($templatePath, $contents[$key]);
            }
        }
    }










    // Ajout d'une fonction Twig pour générer dynamiquement les champs du formulaire
    private function generateFormFields(array $formFields): string
    {
        $html = '';
        foreach ($formFields as $field => $type) {
            $html .= "<div class=\"form-group\">
                    <input type=\"{$type}\" id=\"{$field}\" name=\"{$field}\" placeholder=\"" . ucfirst($field) . "\" class=\"form-control\" required />
                  </div>";
        }
        return $html;
    }






    #[Route('/view/{slug}', name: 'view_by_slug', methods: ['GET'])]
    public function viewBySlug(string $slug): Response
    {
        // Récupérer les champs du formulaire pour la campagne spécifiée par le slug
        $formFields = $this->getFormFieldsForCampagne($slug);

        // Générer les champs de formulaire en HTML à l'aide de la méthode generateFormFields
        $formHtml = $this->generateFormFields($formFields);

        // Charger la vue Twig correspondant au slug
        $templatePath = "/{$slug}/landing.html.twig";

        return $this->render($templatePath, [
            'slug' => $slug,
            'message' => "Vue chargée pour le slug '{$slug}'",
            'formHtml' => $formHtml, // Passer les champs du formulaire à Twig
            'campagneTitle' => "Campagne {$slug}",
            'slug' => $slug,
        ]);
    }




    private function getFormFieldsForCampagne(string $campagneSlug): array
    {
        // Récupérer toutes les campagnes et les formulaires
        $campagnes = DataCampagne::getAllCampagnes();
        $forms = DataCampagne::getAllForms();

        // Vérifier si la campagne existe
        if (isset($campagnes[$campagneSlug])) {
            $campagne = $campagnes[$campagneSlug];

            // Vérifier si le formulaire associé à la campagne existe dans $forms
            if (isset($forms[$campagne['form']])) {
                return $forms[$campagne['form']]['fields']; // Retourne les champs du formulaire
            }
        }

        return []; // Retourne un tableau vide si la campagne ou le formulaire n'existe pas
    }



    #[Route('/view/{slug}/recap', name: 'post_datas', methods: ['POST'])]
    public function recap(Request $request, string $slug, EntityManagerInterface $db): Response
    {
        // Récupérer les informations de la campagne
        $campagne = DataCampagne::getAllCampagnes();
        $campagne = $campagne[$slug];
        $id_anonceur = $campagne['id_annonceur'];

        // Récupérer les données du formulaire
        $formData = $request->request->all();

        // Ajouter id_annonceur au début du tableau
        $formData = array_merge(['id_annonceur' => $id_anonceur], $formData);


        $this->processFormData($db, $formData);


        $templatePath = "/{$slug}/recap.html.twig";

        // Traiter les données reçues
        // ...

        // Retourner un message de succès
        return $this->render($templatePath, [
            'formData' => $formData,
            'campagneTitle' => "Campagne {$slug}",
            'campagne' => $campagne,
        ]);
    }



    private function processFormData(EntityManagerInterface $db, array $data): void
    {
        // Colonnes définies dans la table
        $columns = ['id_annonceur', 'prenom', 'nom', 'email', 'adresse', 'date_naissance', 'code_postal', 'ville', 'telephone'];

        // Initialisation des données à insérer
        $mappedData = [];
        $others = [];

        // Parcourir les données d'entrée
        foreach ($data as $key => $value) {
            if (in_array($key, $columns)) {
                // Si la clé correspond à une colonne, on la garde pour une insertion directe
                $mappedData[$key] = $value;
            } else {
                // Sinon, elle va dans "autres"
                $others[$key] = $value;
            }
        }

        // Ajouter les données restantes dans le champ "autres" sous forme de JSON
        $mappedData['autres'] = json_encode($others);

        // Préparer et exécuter l'insertion
        $connection = $db->getConnection();
        $queryBuilder = $connection->createQueryBuilder();

        $queryBuilder->insert('table_collecte');

        foreach ($mappedData as $column => $value) {
            $queryBuilder->setValue("`$column`", ":$column");
            $queryBuilder->setParameter($column, $value);
        }

        //Debug SQL avant exécution
        // var_dump($queryBuilder->getSQL(), $queryBuilder->getParameters());
        // exit;


        try {
            $queryBuilder->executeStatement();
        } catch (\Exception $e) {
            // Affiche l'erreur SQL pour le debug
            var_dump($e->getMessage());
            exit;
        }
        
    }
    
}
