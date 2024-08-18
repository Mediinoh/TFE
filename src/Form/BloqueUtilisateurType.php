<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class BloqueUtilisateurType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $builder->add('bloque', HiddenType::class, [
            'data' => $options['bloque'],
        ])
        ->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn ' . ($options['bloque'] ? 'btn-danger' : 'btn-success'),
            ],
            'label' => $options['bloque'] ? 
                        '<span class="bi bi-lock" aria-hidden="true"></span> ' . $this->translator->trans('block_user', [], 'messages', $locale)
                        : '<span class="bi bi-unlock" aria-hidden="true"></span> ' . $this->translator->trans('unblock_user', [], 'messages', $locale),
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
