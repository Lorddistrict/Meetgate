<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Passwords are not the same',
                'options' => [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ],
                'required' => true,
                'first_options'  => [
                    'label' => 'New password (6 characters min)',
                    'attr' => [
                        'class' => 'form-control form-spacer',
                        ''
                    ]
                ],
                'second_options' => [
                    'label' => 'Repeat the new password',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
