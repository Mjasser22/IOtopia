<?php

namespace App\Form;

use App\Entity\Animal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez un nom'],
                'empty_data' => '',  // <-- important so it never passes null
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                    new Assert\Length(['min' => 3, 'minMessage' => 'Le nom doit contenir au moins 3 caractères']),
                ]
            ])
            ->add('species', TextType::class, [
                'label' => 'Espèce',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l\'espèce'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'espèce est obligatoire']),
                ]
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l\'âge'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'âge est obligatoire']),
                    new Assert\Positive(['message' => 'L\'âge doit être un nombre positif']),
                ]
            ])
            ->add('healthStatus', ChoiceType::class, [
                'label' => 'État de santé',
                'choices' => [
                    'Healthy' => 'Healthy',
                    'Sick' => 'Sick',
                    'Injured' => 'Injured',
                ],
                'placeholder' => 'Sélectionnez un état de santé',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un état de santé valide']),
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Sauvegarder',
                'attr' => ['class' => 'btn btn-success']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
        ]);
    }
}