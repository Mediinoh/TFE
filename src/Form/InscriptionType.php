<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class InscriptionType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '50',
                    'autocomplete' => 'family-name',
                ],
                'label' => $this->translator->trans('lastname', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 50]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '50',
                    'autocomplete' => 'given-name',
                ],
                'label' => $this->translator->trans('firstname', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 50]),
                ],
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '255',
                    'autocomplete' => 'street-address',
                ],
                'label' => $this->translator->trans('address', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 255]),
                ],
            ])
            ->add('code_postal', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '10',
                    'autocomplete' => 'postal-code',
                ],
                'label' => $this->translator->trans('postal_code', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 10]),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '180',
                    'autocomplete' => 'email',
                ],
                'label' => $this->translator->trans('email_address', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\Email(),
                    new Assert\Length(['min' => 2, 'max' => 180]),
                ],
            ])
            ->add('date_naissance', BirthdayType::class, [
                'input' => 'datetime_immutable',
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'bday',
                ],
                'label' => $this->translator->trans('birth_date', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('pseudo', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '50',
                ],
                'label' => $this->translator->trans('username', [], 'messages', $locale),
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 50]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password',
                    ],
                    'label' => $this->translator->trans('password', [], 'messages', $locale),
                    'label_attr' => [
                        'class' => 'form-label mt-4',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label' => $this->translator->trans('password_confirmation', [], 'messages', $locale),
                    'label_attr' => [
                        'class' => 'form-label mt-4',
                    ],
                ],
                'invalid_message' => $this->translator->trans('password_mismatch', [], 'messages', $locale),
                'constraints' => [
                    new Assert\Length(['min'=> 8, 'minMessage' => $this->translator->trans('password_length', [], 'messages', $locale)]),
                ],
            ])
            ->add('photo_profil', FileType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/png, image/jpeg, *.jpeg, *.jpg, *.png',
                    'autocomplete' => 'photo',
                ],
                'label' => '<span class="bi bi-file-image" aria-hidden="true"></span> ' . $this->translator->trans('profile_picture', [], 'messages', $locale),
                'label_html' => true,
                'required' => false,
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => $this->translator->trans('upload_image_valid', [], 'messages', $locale),
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary mt-4',
                ],
                'label' => '<span class="bi bi-person-add" aria-hidden="true"></span> ' . $this->translator->trans('sign_up', [], 'messages', $locale),
                'label_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
