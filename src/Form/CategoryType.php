<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Form type to handle categories in categories list.
 * /categories
 */
class CategoryType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label_attr' => ['class' => 'hidden']])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Daily' => 'daily',
                    'Major' => 'major',
                    'Home' => 'home',
                ],
                'label_attr' => ['class' => 'hidden']
            ])
            ->add('description', TextType::class, ['label_attr' => ['class' => 'hidden']])
            ->add('save', SubmitType::class, [
                'label' => 'Add Category',
                'attr' => [
                    'class' =>
                        'py-2.5 px-5 me-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700'
                ]
            ])
            ->getForm();
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
