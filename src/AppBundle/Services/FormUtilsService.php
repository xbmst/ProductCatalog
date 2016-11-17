<?php

namespace AppBundle\Services;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilder;

class FormUtilsService
{

    public function getRecoveryForm(FormBuilder $builder)
    {
        $form = $builder
            ->add('email', EmailType::class, [
                'invalid_message' => 'Invalid email'
            ])
            ->getForm();
        return $form;
    }

    public function getRecoveryConfirmationForm(FormBuilder $builder, $url)
    {
        $form = $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Enter new password',
                ],
                'second_options' => [
                    'label' => 'Repeat new password',
                ],
                'invalid_message' => 'Passwords do not match'
            ])
            ->setAction($url)
            ->getForm();
        return $form;
    }

}
