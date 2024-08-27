<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageReactionType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private RequestStack $requestStack)
    {

    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $reaction = $options['reaction'];
        $liked = 'like' === $reaction;

        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $builder
            ->add($reaction, SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-sm me-2 ' . ($liked ? 'btn-outline-primary' : 'btn-outline-danger'),
                ],
                'label' => ($liked ?  '<span class="bi bi-hand-thumbs-up" aria-hidden="true"></span> ' : '<span class="bi bi-hand-thumbs-down" aria-hidden="true"></span> ') . $this->translator->trans($reaction, [], 'messages', $locale) . ' ' . $options['reactionCount'],
                'label_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'reaction' => 'like',
            'reactionCount' => 0,
        ]);
    }
}
