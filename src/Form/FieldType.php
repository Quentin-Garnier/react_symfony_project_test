<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du champ',
                'required' => true,
            ])
            ->add('value', ChoiceType::class, [
                'label' => 'Valeur',
                'required' => true,
                'choices' => [
                    'int' => 'int',
                    'str' => 'str',
                    'bool' => 'bool',
                    'float' => 'float',
                ],
                'placeholder' => 'Sélectionner une option', // Permet d'afficher un texte par défaut
            ])
            ->add('rule', TextType::class, [
                'label' => 'Règle',
                'required' => false,
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Requis ?',
                'required' => false,
            ]);
    }
}
