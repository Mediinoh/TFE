<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjoutCategorieType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $builder
            ->add('nom_categorie', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => $this->translator->trans('category_name', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('ajout_categorie', SubmitType::class, [
                'label' => '<span class="bi bi-plus-circle" aria-hidden="true"></span> ' . $this->translator->trans('add_category', [], 'messages', $locale),
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
