<?php
declare(strict_types=1);
namespace App\Form;

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
    ->add('name', FormTypes\TextType::class, [
      'constraints' => [
        new Assert\NotBlank(),
        new Assert\Length(['min' => 2, 'max' => 255]),
      ],
      'label' => 'Name',
      'attr' => [
        'placeholder' => 'Enter your name',
      ],
    ])
    ->add('email', FormTypes\EmailType::class, [
      'constraints' => [
        new Assert\NotBlank(),
        new Assert\Email(),
      ],
      'label' => 'Email',
      'attr' => [
        'placeholder' => 'Enter your email address',
      ],
    ])
    ->add('message', FormTypes\TextareaType::class, [
      'constraints' => [
        new Assert\NotBlank(),
        new Assert\Length(['min' => 10]),
      ],
      'label' => 'Message',
      'attr' => [
        'placeholder' => 'Enter your message',
        'rows' => 8,
      ],
    ])
    ->add('captcha', Recaptcha3Type::class, [
      'constraints' => new Recaptcha3(),
      'action_name' => 'contactform',
              //'script_nonce_csp' => $nonceCSP,
      'locale' => 'en',
    ])
    ->add('submit', FormTypes\SubmitType::class, [
      'label' => 'Submit',
    ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
    // Configure your form options here
    ]);
  }
}
