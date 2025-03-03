<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Recaptcha;


class RecaptchaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Add reCAPTCHA widget (you can customize this part as per your needs)
        $builder->add('recaptcha', HiddenType::class, [
            'data' => 'your-site-key',  // Replace with your actual site key
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getBlockPrefix(): string
    {
        return 'recaptcha';
    }
}
