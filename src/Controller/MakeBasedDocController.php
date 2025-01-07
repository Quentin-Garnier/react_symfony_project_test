<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\DataCampagne;
use Dompdf\Dompdf;
use Dompdf\Options;

class MakeBasedDocController extends AbstractController
{
    #[Route('/makeBasedDoc', name: 'app_make_based_documentation_form')]
    public function index(Request $request): Response
    {
        // Récupérer toutes les campagnes à partir de l'entité DataCampagne
        $campagnes = DataCampagne::getAllCampagnes();

        // Transformer les données en choix pour le formulaire
        $choices = [];
        foreach ($campagnes as $key => $campagne) {
            $choices[$campagne['title']] = $key; // Le titre comme label et le nom de la campagne comme valeur
        }

        // Créer un formulaire dynamique avec des sous-champs correctement configurés
        $form = $this->createFormBuilder()
            ->add('campagne', ChoiceType::class, [
                'label' => 'Choisissez une campagne',
                'choices' => $choices,
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])

            ->add('generate', SubmitType::class, [
                'label' => 'Générer la documentation',
            ])
            ->setMethod('POST')
            ->setAction($this->generateUrl('app_generate_based_documentation'))
            ->getForm();

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données soumises
            $data = $form->getData();

            // Rediriger après traitement
            return $this->redirectToRoute('app_generate_based_documentation', [
                'campagne' => $data['campagne'],
            ]);
        }

        // Afficher le formulaire
        return $this->render('make_based_doc/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/generateBasedDocumentation', name: 'app_generate_based_documentation')]
    public function generateDocumentation(Request $request): Response
    {
        // Récupérer les données soumises depuis le formulaire
        $selectedCampagne = $request->request->all();
        $selectedCampagne = $selectedCampagne['form']['campagne'];

        // Récupérer les campagnes et les formulaires
        $campagnes = DataCampagne::getAllCampagnes();
        $forms = DataCampagne::getAllForms();

        // Vérifier si la campagne sélectionnée existe
        if (!isset($campagnes[$selectedCampagne])) {
            throw $this->createNotFoundException('La campagne sélectionnée n\'existe pas.');
        }

        // Récupérer les détails de la campagne et du formulaire associé
        $campagneDetails = $campagnes[$selectedCampagne];
        $formName = $campagneDetails['form'];
        $formDetails = $forms[$formName] ?? null;

        if (!$formDetails) {
            throw $this->createNotFoundException('Le formulaire associé à cette campagne est introuvable.');
        }

        // Préparer le contenu HTML pour le PDF
        $htmlContent = $this->renderView('make_based_doc/documentation_pdf.html.twig', [
            'campagne' => $campagneDetails,
            'form' => $formDetails,
        ]);

        // Créer une instance de DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le PDF et le sauvegarder temporairement
        $pdfOutput = $dompdf->output();
        $fileName = 'documentation-' . $selectedCampagne . '.pdf';
        
        // Retourner la réponse pour téléchargement
        return new Response(
            $pdfOutput,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }


    

    #[Route('/sendLead', name: 'app_send_lead')]
    public function sendLead(Request $request, EntityManagerInterface $db): Response
    {
        // Récupérer les données JSON du corps de la requête
        $content = $request->getContent();
        $formData = json_decode($content, true);

        // Vérifier que les données JSON sont valides
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer toutes les campagnes pour valider l'id_annonceur
        $campagnes = DataCampagne::getAllCampagnes();
        $forms = DataCampagne::getAllForms();

        // Vérifier que l'id_annonceur est présent et valide
        $campagne = array_filter($campagnes, function ($campagne) use ($formData) {
            return isset($formData['id_annonceur']) && $campagne['id_annonceur'] === $formData['id_annonceur'];
        });

        if (!$campagne) {
            return $this->json(['error' => 'Invalid or missing id_annonceur'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer la campagne et le formulaire associé
        $campagneDetails = reset($campagne); // Récupère la première campagne correspondante
        $formName = $campagneDetails['form'];
        $formDetails = $forms[$formName] ?? null;

        if (!$formDetails) {
            return $this->json(['error' => 'Formulaire associé à cette campagne introuvable'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier que tous les champs requis sont présents dans les données
        $requiredFields = array_keys($formDetails['fields']);
        $missingFields = array_diff($requiredFields, array_keys($formData));

        if (!empty($missingFields)) {
            return $this->json(['error' => 'Missing required fields', 'fields' => $missingFields], Response::HTTP_BAD_REQUEST);
        }

        // Insérer les données dans la base de données
        try {
            $this->processFormData($db, $formData);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to insert data: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retourner une réponse JSON de succès
        return $this->json(['success' => true], Response::HTTP_OK);
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

        try {
            $queryBuilder->executeStatement();
        } catch (\Exception $e) {
            // Affiche l'erreur SQL pour le debug
            var_dump($e->getMessage());
            exit;
        }
    }
}
