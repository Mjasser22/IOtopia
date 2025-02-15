<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Commentaire',
                'constraints' => [
                    new NotBlank(['message' => 'Le commentaire ne peut pas être vide.']),
                    new Length([
                        'max' => 500,
                        'maxMessage' => 'Le commentaire ne peut pas dépasser 500 caractères.'
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ajouter un commentaire...',
                    'rows' => 3
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
