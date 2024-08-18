<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use Symfony\Component\Validator\Constraints as Assert;

class PaymentType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $annee = date('Y');

        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'name',
                ],
                'label' => $this->translator->trans('name_card', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z]+ [a-zA-Z]+$/',
                    ]),
                ],
            ])
            ->add('numeroCarte', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'cc-number'
                ],
                'label' => $this->translator->trans('card_number', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form_label',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 6,
                    ]),
                ],
            ])
            ->add('moisExpiration', ChoiceType::class, [
                'label' => $this->translator->trans('expiration_month', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'choices' => array_combine(range(1, 12), range(1, 12)),
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'cc-exp-month'
                ],
            ])
            ->add('anneeExpiration', ChoiceType::class, [
                'label' => $this->translator->trans('expiration_year', [], 'messages', $locale),
                'choices' => array_combine(range($annee, $annee + 10), range($annee, $annee + 10)),
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'cc-exp-year',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('cvc', TextType::class, [
                'label' => $this->translator->trans('cvc', [], 'messages', $locale),
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'cc-csc',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'constraints' => [
                    new Assert\Blank(),
                    new Assert\Length(['min' => 3, 'max' => 3]),
                    new Assert\Regex([
                        'pattern' => '/^\d{3}$/' ])
                ],
            ])
            ->add('valider', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-success',
                ],
                'label' => $this->translator->trans('confirm_payment', [], 'messages', $locale),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
