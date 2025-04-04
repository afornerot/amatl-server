<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('submit', SubmitType::class, [
            'label' => 'Valider',
            'attr' => ['class' => 'btn btn-success no-print'],
        ])

        ->add('title', TextType::class, [
            'label' => 'Nom',
        ])

        ->add('logo', HiddenType::class)

        ->add('gitUrl', TextType::class, [
            'label' => 'URL du dépôt Git',
            'required' => false,
            'attr' => ['autocomplete' => 'off'],
        ])

        ->add('gitUsername', TextType::class, [
            'label' => 'Nom d’utilisateur Git',
            'required' => false,
            'attr' => ['autocomplete' => 'off'],
        ])
        
        ->add('gitToken', PasswordType::class, [
            'label' => 'Token Git',
            'required' => false,
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
        ])
                
        ->add('users', EntityType::class, [
            'label' => 'Utilisateurs',
            'class' => User::class,
            'choice_label' => 'username',
            'multiple' => true,
            'attr' => ['class' => 'select2'],
            'required' => false,
            'by_reference' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'mode' => 'submit',
        ]);
    }
}
