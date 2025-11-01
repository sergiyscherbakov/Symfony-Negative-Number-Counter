<?php

namespace App\Form;

use App\Entity\NumberArray;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class NumberArrayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numbers', TextareaType::class, [
                'label' => 'Введіть числа (через кому)',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Наприклад: -5, 10, -3, 7, -1, 0, 15',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Будь ласка, введіть числа'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^-?\d+(\.\d+)?(,\s*-?\d+(\.\d+)?)*$/',
                        'message' => 'Введіть числа через кому (наприклад: -5, 10, -3, 7)'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NumberArray::class,
        ]);
    }
}
