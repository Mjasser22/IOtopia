<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your current password.']),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'New Password'],
                'second_options' => ['label' => 'Confirm New Password'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a new password.']),
                    new Length([
                        'min' => 8,
                        'max' => 20,
                        'minMessage' => 'Your password should be at least {{ limit }} characters.',
                        'maxMessage' => 'Your password should not exceed {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Change Password']);
    }
}
