<?php

// src/Form/CampagneType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\CampagneFieldType;
use Symfony\Component\Form\FormEvents;

class CampagneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campagne', TextType::class, [
                'label' => 'Nom de la campagne',
                'required' => true,
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('id_annonceur', TextType::class, [
                'label' => 'ID Annonceur',
                'required' => true,
            ])
            ->add('active', ChoiceType::class, [
                'label' => 'Active',
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
            ])
            ->add('form', TextType::class, [
                'label' => 'Form Name',
                'required' => true,
            ])
            ->add('postback', TextType::class, [
                'label' => 'Postback',
                'required' => true,
            ])
            ->add('url_postback', TextType::class, [
                'label' => 'URL Postback',
                'required' => true,
            ])
            ->add('no_ws', ChoiceType::class, [
                'label' => 'No WS',
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
            ])
            ->add('ws_model', TextType::class, [
                'label' => 'WS Model',
                'required' => false,
            ])
            ->add('ws', TextType::class, [
                'label' => 'WS',
                'required' => false,
            ])
            ->add('form_fields', CollectionType::class, [
                'label' => false,
                'entry_type' => CampagneFieldType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
