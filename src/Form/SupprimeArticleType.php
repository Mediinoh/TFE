<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupprimeArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supprime', HiddenType::class, [
                'data' => $options['supprime'],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn ' . ($options['supprime'] ? 'btn-danger' : 'btn-success'),
                ],
                'label' => $options['supprime'] ? 
                            '<span class="bi bi-trash" aria-hidden="true"></span> Supprimer l\'article'
                            : '<span class="bi bi-arrow-counterclockwise" aria-hidden="true"></span> Réinjecter l\'article',
                'label_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'supprime' => false,
        ]);
    }
}
