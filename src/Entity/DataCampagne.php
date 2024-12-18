<?php

namespace App\Entity;

class DataCampagne
{
    // Tableau statique contenant les campagnes
    private static array $campagnes = array (
  '2024-12-winter-sale' => 
  array (
    'campagne' => '2024-12-winter-sale',
    'title' => 'Winter Sale 2024',
    'id_annonceur' => 1234,
    'active' => true,
    'form' => 'form_winter_sale',
    'postback' => '',
    'url_postback' => '',
    'no_ws' => true,
  ),
  '2024-12-amazon-cybermonday' => 
  array (
    'campagne' => '2024-12-amazon-cybermonday',
    'title' => 'Amazon Cyber Monday 2024',
    'id_annonceur' => 1250,
    'active' => true,
    'form' => 'form_amazon_cybermonday',
    'postback' => '',
    'url_postback' => '',
    'data_webservice' => 
    array (
      'ws_model' => 'webservices/Api_amazon_cybermonday_model',
      'ws' => 'PreparationEnvoiLead',
    ),
  ),
);


    private static array $forms = array (
  'form_winter_sale' => 
  array (
    'fields' => 
    array (
      'prenom' => 'text',
      'nom' => 'text',
      'email' => 'email',
      'telephone' => 'text',
      'entreprise' => 'text',
      'fonction' => 'text',
      'nb_salaries' => 'number',
    ),
  ),
  'form_amazon_cybermonday' => 
  array (
    'fields' => 
    array (
      'nom' => 'text',
      'email' => 'email',
      'telephone' => 'tel',
      'adresse' => 'text',
      'code_postal' => 'text',
      'ville' => 'text',
      'pays' => 'text',
    ),
  ),
);

    





    
    // Méthode pour récupérer toutes les campagnes
    public static function getAllCampagnes(): array
    {
        return self::$campagnes;
    }

    // Méthode pour récupérer tous les forms
    public static function getAllForms(): array
    {
        return self::$forms;
    }

    



    // Méthode pour ajouter une nouvelle campagne
    public static function addCampagne(array $newCampagne): void
    {
        // Ajouter la nouvelle campagne au début du tableau
        self::$campagnes = [$newCampagne['campagne'] => $newCampagne] + self::$campagnes;
    }



    // Méthode pour ajouter un nouveau form
    public static function addForm(array $data): void
    {
        // Vérifier que le champ 'campagne' contient une clé 'form'
        if (!isset($data['campagne']['form'])) {
            throw new \Exception("Le champ 'form' est manquant dans 'campagne'.");
        }

        // Vérifier que le champ 'form' est une liste d'éléments
        if (!isset($data['form']) || !is_array($data['form'])) {
            throw new \Exception("Le champ 'form' doit contenir une liste de champs.");
        }

        $fields = [];

        foreach ($data['form'] as $field) {
            if (isset($field['field'], $field['type'])) {
                $fields[$field['field']] = $field['type']; // Ajouter la clé et sa valeur
            } else {
                throw new \Exception("Chaque champ doit contenir les clés 'field' et 'type'.");
            }
        }


        // Ajouter le formulaire dans le tableau $forms
        $formName = $data['campagne']['form']; // Récupérer le nom du formulaire
        self::$forms = [$formName => ['fields' => $fields]] + self::$forms;

    }

    
}
