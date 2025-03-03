<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

class AnimalHealthFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('animalType', ChoiceType::class, [
                'choices' => array_flip($options['animals']),
                'label' => 'Animal Type',
                'placeholder' => 'Select an animal',
                'constraints' => [
                    new NotBlank(['message' => 'Please select an animal type'])
                ],
                'attr' => [
                    'class' => 'form-select',
                    'data-test' => 'animal-select'
                ]
            ])
            ->add('symptoms', ChoiceType::class, [
                'choices' => array_flip($options['symptoms']),
                'label' => 'Select Symptoms (1-5)',
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'max' => 5,
                        'minMessage' => 'Select at least 1 symptom',
                        'maxMessage' => 'Maximum 5 symptoms allowed'
                    ])
                ],
                'attr' => [
                    'class' => 'symptom-checkboxes',
                    'data-max-items' => 5
                ],
                'choice_attr' => function() {
                    return ['class' => 'form-check-input'];
                }
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Analyze Health',
                'attr' => [
                    'class' => 'btn btn-primary w-100 mt-3',
                    'data-loading-text' => 'Analyzing...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'animals' => [],
            'symptoms' => [],
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'animal_health_form',
        ]);
    }
}