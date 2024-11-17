<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PayerType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack, private UrlGeneratorInterface $urlGenerator)
    {

    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        
        $termsUrl = $this->urlGenerator->generate('condition.index');

        $label = sprintf('%s <a href="%s" target=_blank>%s</a>',
        $this->translator->trans('accepted_payment', [], 'messages', $locale),
        $termsUrl, $this->translator->trans('terms_conditions_link', [], 'messages', $locale));

        $builder
            ->add('termsCheck', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input',
                    'id' => 'termsCheck',
                ],
                'label' => $label,
                'label_html' => true,
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
                'required' => true,
                'mapped' => false,
            ])
            ->add('payer', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                    'id' => 'checkout-button',
                    'disabled' => 'disabled',
                ],
                'label' => '<span class="bi bi-cart-fill" aria-hidden="true"></span> ' . $this->translator->trans('pay', [], 'messages', $locale),
                'label_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Nothing entity
        ]);
    }
}
