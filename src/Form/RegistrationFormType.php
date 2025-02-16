<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
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
        ->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'constraints' => [
                new NotBlank(['message' => 'Please enter a password.']),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Your password must be at least {{ limit }} characters.',
                    'max' => 20,
                    'maxMessage' => 'Your password cannot exceed {{ limit }} characters.',
                ]),
            ],
        ])
        ->add('userType', ChoiceType::class, [
            'choices' => [
                'Employer' => 'employer',
                'Employee' => 'employee',
            ],
            'expanded' => false,  
            'multiple' => false,  
            'placeholder' => 'Select a role', 
            'constraints' => [
                new NotBlank(['message' => 'Please select a role.']),
            ],
        ])
        ->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'constraints' => [
                new IsTrue(['message' => 'You must agree to the terms.']),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
