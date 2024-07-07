<?php

namespace App\Form;

use LogicException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Form type to get all expenses by day.
 * /expenses-by-date
 */
class DayType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new LogicException(
                'The FriendMessageFormType cannot be used without an authenticated user!'
            );
        }

        $builder
            ->add('previous', SubmitType::class, [
                'label' => 'Previous Day',
                'attr' => [
                    'class' => 'arrow-button-styles'
                ]
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'input' => 'string',
                'label_attr' => ['class' => 'hidden']
            ])
            ->add('next', SubmitType::class, [
                'label' => 'Next Day',
                'attr' => [
                    'class' => 'arrow-button-styles'
                ]
            ])->add('save', SubmitType::class, [
                    'label' => 'Go to selected day',
                    'attr' => [
                        'class' =>
                            'text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800'
                    ]
                ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
