<?php

namespace App\Form;

use App\Entity\Categorie;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FiltreArticleParType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        
        $builder
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('c')
                                ->orderBy('c.id', 'ASC');
                },
                'label' => $this->translator->trans('category', [], 'messages', $locale),
                'choice_label' => 'nomCategorie',
                'required' => false,
                'placeholder' => $this->translator->trans('all_categories', [], 'messages', $locale),
                'attr' => [
                    'class' => 'form-select',
                ],
                'label_attr' => [
                    'class' => 'form-label me-3 d-flex align-items-center',
                ],
            ])
            ->add('mot_cle', SearchType::class, [
                'required' => false,
                'label' => $this->translator->trans('keyword', [], 'messages', $locale),
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('rechercher', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
                'label_html' => true,
                'label' => '<span class="bi bi-search-heart" aria-hidden="true"></span> ' . $this->translator->trans('search', [], 'messages', $locale),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
        ]);
    }
}
