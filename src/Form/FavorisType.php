<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FavorisType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $builder->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn ' . ($options['isFavorite'] ? 'btn-outline-warning' : 'btn-outline-danger'),
                'title' => $this->translator->trans($options['isFavorite'] ? 'remove_favorite' : 'add_favorite', [], 'messages', $locale),
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
