<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BloqueUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('bloque', HiddenType::class, [
            'data' => $options['bloque'],
        ])
        ->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn ' . ($options['bloque'] ? 'btn-danger' : 'btn-success'),
            ],
            'label' => $options['bloque'] ? 
                        '<span class="bi bi-lock" aria-hidden="true"></span> Bloquer l\'utilisateur'
                        : '<span class="bi bi-unlock" aria-hidden="true"></span> Débloquer l\'utilisateur',
            'label_html' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'bloque' => false,
        ]);
    }
}
