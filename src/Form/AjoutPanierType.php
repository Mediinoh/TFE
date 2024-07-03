<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutPanierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-select',
                ],
                'label' => 'Quantité',
                'choices' => array_combine(range(1, $options['quantiteMax']), range(1, $options['quantiteMax'])),
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('ajout_panier', SubmitType::class, [
                'label' => '<span class="bi bi-cart-plus" aria-hidden="true"></span> Ajouter au panier',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'quantiteMax' => 10,
            'csrf_protection' => true,
        ]);
    }
}
