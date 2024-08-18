<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuppressionArticlePanierType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {

    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $quantiteMax = $options['quantiteMax'];
        
        $builder
            ->add('quantite', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-select',
                ],
                'data' => $quantiteMax,
                'label' => $this->translator->trans('quantity', [], 'messages', $locale),
                'choices' => array_combine(range(1, $quantiteMax), range(1, $quantiteMax)),
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('suppression_panier', SubmitType::class, [
                'label' => '<span class="bi bi-trash" aria-hidden="true"></span> ' . $this->translator->trans('delete', [], 'messages', $locale),
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-danger',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'quantiteMax' => 1,
        ]);
    }
}
