<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('submit', SubmitType::class, [
            'label' => 'Valider',
            'attr' => ['class' => 'btn btn-success no-print'],
        ])

        ->add('username', TextType::class, [
            'label' => 'Login',
        ])

        ->add('avatar', HiddenType::class)

        ->add('email', EmailType::class, [
            'label' => 'Email',
        ]);

        if ('profil' != $options['mode']) {
            $builder
            ->add('roles', ChoiceType::class, [
                'choices' => ['ROLE_ADMIN' => 'ROLE_ADMIN', 'ROLE_USER' => 'ROLE_USER'],
                'multiple' => true,
                'expanded' => true,
            ])

            ->add('projects', EntityType::class, [
                'label' => 'Projets',
                'class' => Project::class,
                'choice_label' => 'title',
                'multiple' => true,
                'attr' => ['class' => 'select2'],
            ]);
        }

        if('SQL' === $options['modeAuth']) {
            $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => ('submit' == $options['mode'] ? true : false),
                'options' => ['always_empty' => true],
                'first_options' => ['label' => 'Mot de Passe', 'attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px', 'autocomplete' => 'new-password']],
                'second_options' => ['label' => 'Confirmer Mot de Passe', 'attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']],
            ]);        
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'mode' => 'submit',
            'modeAuth' => 'SQL',
        ]);
    }
}
