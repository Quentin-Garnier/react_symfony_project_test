<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Form\FieldType;

class MakeDocumentationController extends AbstractController
{   
    #[Route('/makeDoc', name: 'app_make_documentation_form')]
    public function index(Request $request): Response
    {
        // Créer un formulaire dynamique avec des sous-champs correctement configurés
        $form = $this->createFormBuilder()
            ->add('docName', TextType::class, [
                'label' => 'Nom de la documentation',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
            ->add('url', TextType::class, [
                'label' => 'URL de la documentation',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])

            ->add('fields', CollectionType::class, [
                'entry_type' => FieldType::class, // Type pour chaque élément de la collection
                'entry_options' => [
                    'label' => false, // Pas de label global
                ],
                'allow_add' => true, // Permet d'ajouter dynamiquement
                'allow_delete' => true, // Permet de supprimer dynamiquement
                'prototype' => true, // Prototype pour les nouveaux champs
                'prototype_name' => '__name__', // Nom de l'index du prototype
                'by_reference' => false,
                'label' => false, // Pas de label global
            ])
            ->add('generate', SubmitType::class, [
                'label' => 'Générer la documentation',
            ])
            ->setMethod('POST')
            ->setAction($this->generateUrl('app_generate_documentation'))
            ->getForm();

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données soumises
            $data = $form->getData();

            // Rediriger après traitement
            return $this->redirectToRoute('app_generate_documentation');
        }

        // Afficher le formulaire
        return $this->render('make_documentation/make_documentation.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/generateDoc', name: 'app_generate_documentation', methods: ['POST'])]
    public function generatePdf(Request $request): Response
    {
        $formData = $request->request->all();

        if (isset($formData['form'])) {
            $formData = $formData['form'];
        } 

        // Vérifier que les données existent
        if (!$formData || !isset($formData['fields'])) {
            return new Response('Aucun champ fourni', 400);
        }

        $docName = $formData['docName'] ?? 'documentation';
        $url = $formData['url'] ?? '404';
        $fields = $formData['fields'] ?? [];

        if (empty($fields)) {
            return new Response('Aucun champ fourni', 400);
        }

        // Générer le HTML pour le PDF
        $html = $this->renderView('make_documentation/pdf_template.html.twig', [
            'docName' => $docName,
            'url' => $url,
            'fields' => $fields,
        ]);

        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);

        // Retourner le PDF en réponse
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.pdf"',
        ]);
    }
}
