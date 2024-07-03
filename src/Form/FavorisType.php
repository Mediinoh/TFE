<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FavorisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn ' . ($options['isFavorite'] ? 'btn-outline-warning' : 'btn-outline-danger'),
                'title' => $options['isFavorite'] ? 'Supprimer le favori' : 'Ajouter un favori',
            ],
            'label_html' => true,
            'label' => '<span class="bi bi-star" aria-hidden="true"></span>',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'isFavorite' => true,
        ]);
    }
}
