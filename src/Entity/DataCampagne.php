<?php

namespace App\Entity;

class DataCampagne
{
    // Tableau statique contenant les campagnes
    private static array $campagnes = array (
  '2025-04-easter-promo' => 
  array (
    'campagne' => '2025-04-easter-promo',
    'title' => 'Easter Promotion 2025',
    'id_annonceur' => 1400,
    'active' => true,
    'form' => 'form_easter_promo',
    'postback' => '',
    'url_postback' => '',
    'no_ws' => false,
    'data_webservice' => 
    array (
      'ws_model' => 'Api_easter_promo_model',
      'ws' => 'RegisterEasterParticipant',
    ),
  ),
  '2025-02-valentinesday-special' => 
  array (
    'campagne' => '2025-02-valentinesday-special',
    'title' => 'Valentine\'s Day Special 2025',
    'id_annonceur' => 1300,
    'active' => true,
    'form' => 'form_valentinesday_special',
    'postback' => '',
    'url_postback' => '',
    'no_ws' => false,
    'data_webservice' => 
    array (
      'ws_model' => 'Api_valentinesday_special_model',
      'ws' => 'SubmitLeadData',
    ),
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
    'no_ws' => false,
    'data_webservice' => 
    array (
      'ws_model' => 'Api_amazon_cybermonday_model',
      'ws' => 'PreparationEnvoiLead',
    ),
  ),
  '2025-06-summer-discount' => 
  array (
    'campagne' => '2025-06-summer-discount',
    'title' => 'Summer Discount 2025',
    'id_annonceur' => 5678,
    'active' => true,
    'form' => 'form_summer_discount',
    'postback' => '',
    'url_postback' => '',
    'no_ws' => false,
    'data_webservice' => 
    array (
      'ws_model' => 'Api_summer_discount_model',
      'ws' => 'SendDiscountLead',
    ),
  ),
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
);


    private static array $forms = array (
  'form_easter_promo' => 
  array (
    'fields' => 
    array (
      'full_name' => 'text',
      'email' => 'email',
      'phone_number' => 'tel',
      'street_address' => 'text',
      'postal_code' => 'text',
      'city' => 'text',
      'country' => 'text',
      'favorite_easter_treat' => 'text',
    ),
  ),
  'form_valentinesday_special' => 
  array (
    'fields' => 
    array (
      'first_name' => 'text',
      'last_name' => 'text',
      'email' => 'email',
      'phone' => 'tel',
      'address' => 'text',
      'zip_code' => 'text',
      'city' => 'text',
      'country' => 'text',
      'gift_preference' => 'text',
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
  'form_summer_discount' => 
  array (
    'fields' => 
    array (
      'prenom' => 'text',
      'nom' => 'text',
      'email' => 'email',
      'telephone' => 'text',
      'adresse' => 'text',
      'code_postal' => 'text',
      'ville' => 'text',
      'company_name' => 'text',
      'job_title' => 'text',
      'company_size' => 'number',
      'budget' => 'number',
    ),
  ),
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
