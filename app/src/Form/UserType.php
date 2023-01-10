<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Define the form fields
        $builder
            ->add('username')
            ->add('email')
            ->add('password', PasswordType::class)
            ->add('firstname')
            ->add('lastname')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => User::ADMIN_ROLE,
                    'User' => User::USER_ROLE
                ]
            ])
        ;

        $builder->get('roles')->addModelTransformer(new CallbackTransformer(
            function($roles){
                return in_array(User::ADMIN_ROLE, $roles) ? User::ADMIN_ROLE : User::USER_ROLE;
            },
            function($rolesAsString){
                return $rolesAsString === User::ADMIN_ROLE ? [User::ADMIN_ROLE] : [User::USER_ROLE];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set the form options
        $resolver->setDefaults(array(
            'data_class' => User::class
        ));
    }
}
