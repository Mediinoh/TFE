<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class SupprimeArticleType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $builder
            ->add('supprime', HiddenType::class, [
                'data' => $options['supprime'],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn ' . ($options['supprime'] ? 'btn-danger' : 'btn-success'),
                ],
                'label' => $options['supprime'] ? 
                            '<span class="bi bi-trash" aria-hidden="true"></span> ' . $this->translator->trans('delete_article', [], 'messages', $locale)
                            : '<span class="bi bi-arrow-counterclockwise" aria-hidden="true"></span> ' . $this->translator->trans('reinstate_article', [], 'messages', $locale),
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
