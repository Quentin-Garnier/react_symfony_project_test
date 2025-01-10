<?php

// src/Form/CampagneFieldType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;

class CampagneFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('field', TextType::class, [
                'label' => 'Nom du champ',
                'required' => true,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9]+$/',
                        'message' => 'The field can only contain letters and numbers.',
                    ]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type du champ',
                'choices' => [
                    'int' => 'int',
                    'str' => 'str',
                    'bool' => 'bool',
                    'float' => 'float',
                ],
            ]);
    }
}