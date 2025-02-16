<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Email cannot be empty.']),
                new Email(['message' => 'Please enter a valid email address.']),
            ],
        ])
        ->add('firstname', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'First name cannot be empty.']),
            ],
        ])
        ->add('lastname', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Last name cannot be empty.']),
            ],
        ])
        ->add('password', PasswordType::class, [
            'label' => 'Password',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a password',
                    'groups' => ['create']
                ]),
            ],
        ])
        ->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'constraints' => [
                new IsTrue(['message' => 'You must agree to the terms.']),
            ],
        ])
        ->add('image', FileType::class, [
            'label' => 'Profile Image',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                    'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, GIF).',
                ])
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
