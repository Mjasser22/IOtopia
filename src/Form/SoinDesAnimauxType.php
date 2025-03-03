<?php

namespace App\Form;

use App\Entity\Animal;
use App\Entity\SoinDesAnimaux;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoinDesAnimauxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Description', TextType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez la description'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est obligatoire']),
                ]
            ])
            ->add('start_date', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text', // Utilise un input de type date HTML5
                'html5' => true,           // Active le sélecteur de date natif
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d') // Empêche les dates passées
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de début est obligatoire']),
                    new Assert\Type([
                        'type' => \DateTimeInterface::class, 
                        'message' => 'Entrez une date valide'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => (new \DateTime('today')),
                        'message' => 'La date de début ne peut pas être dans le passé'
                    ]),
                ]
            ])
            
            
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (en jours)',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez la durée'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La durée est obligatoire']),
                    new Assert\Positive(['message' => 'La durée doit être un nombre positif']),
                ]
            ])
            ->add('animal', EntityType::class, [
                'class' => Animal::class,
                'choice_label' => 'name', 
                'label' => 'Animal',
                'placeholder' => 'Sélectionnez un animal',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un animal']),
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
            'data_class' => SoinDesAnimaux::class,
        ]);
    }
}
