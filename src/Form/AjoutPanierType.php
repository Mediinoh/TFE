<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjoutPanierType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $quantity = min($options['quantiteMax'], $options['stock']); 
        $quantityMin = intval($quantity > 0);

        $builder
            ->add('quantite', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-select',
                ],
                'label' => $this->translator->trans('quantity', [], 'messages', $locale),
                'choices' => array_combine(range($quantityMin, $quantity), range($quantityMin, $quantity)),
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('ajout_panier', SubmitType::class, [
                'label' => '<span class="bi bi-cart-plus" aria-hidden="true"></span> ' .$this->translator->trans('add_to_cart', [], 'messages', $locale),
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary',
                    'disabled' => !$quantityMin,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'quantiteMax' => 10,
            'stock' => 10,
            'csrf_protection' => true,
        ]);
    }
}
