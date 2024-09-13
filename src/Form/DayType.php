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
                'label' => '< Previous Day',
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
                'label' => 'Next Day >',
                'attr' => [
                    'class' => 'arrow-button-styles'
                ]
            ])->add('save', SubmitType::class, [
                    'label' => 'Go to selected day',
                    'attr' => [
                        'class' =>
                            'py-2.5 px-5 me-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700'
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
