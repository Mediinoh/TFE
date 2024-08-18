<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Categorie;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjoutArticleType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $builder
            ->add('titre', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '255',
                ],
                'label' => $this->translator->trans('title', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 255]),
                ],
            ])
            ->add('prix_unitaire', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 0.01,
                    'value' => 0,
                ],
                'label' => $this->translator->trans('unit_price', [], 'messages', $locale),
                'scale' => 2,
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('c')
                                ->orderBy('c.id', 'ASC');
                },
                'label' => '<span class="bi bi-tag" aria-hidden="true"></span> ' . $this->translator->trans('category', [], 'messages', $locale),
                'label_html' => true,
                'choice_label' => 'nomCategorie',
                'required' => false,
                'placeholder' => $this->translator->trans('all_categories', [], 'messages', $locale),
                'attr' => [
                    'class' => 'form-select',
                ],
                'label_attr' => [
                    'class' => 'form-label d-flex align-items-center',
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'cols' => 30,
                    'rows' => 10,
                ],
                'label' => $this->translator->trans('description', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('photo_article', FileType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/png, image/jpeg',
                ],
                'label' => $this->translator->trans('article_photo', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'mapped' => false,
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\File([
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' =>$this->translator->trans('upload_image_valid', [], 'messages', $locale),
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
                'label' => '<span class="bi bi-plus-circle" aria-hidden="true"></span> ' . $this->translator->trans('add_article', [], 'messages', $locale),
                'label_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
