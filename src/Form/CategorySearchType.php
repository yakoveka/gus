<?php

namespace App\Form;

use LogicException;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Form type to get all expenses by day.
 * /expenses-by-date
 */
class CategorySearchType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly ManagerRegistry $managerRegistry,
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

        $userId = $user->getId();

        $choices = $this->getCategoriesChoicesByType($options['data']['type'] ?? 'daily', $user->getId());

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Daily' => 'daily',
                    'Major' => 'major',
                    'Home' => 'home',
                ],
                'label_attr' => ['class' => 'hidden']
            ])
            ->add('categoryId', ChoiceType::class, ['choices' => $choices, 'label_attr' => ['class' => 'hidden']])
            ->add('save', SubmitType::class, [
                'label' => 'Go to selected category',
                'attr' => [
                    'class' =>
                        'py-2.5 px-5 me-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700'
                ]
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($userId) {
            $form = $event->getForm();
            $data = $event->getData();

            $type = $data['type'];

            $choices = $this->getCategoriesChoicesByType($type, $userId);

            $form->add('categoryId', ChoiceType::class, [
                'choices' => $choices,
            ]);
        });
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

    /**
     * Prepare categories as choices for specific type and user.
     *
     * @param string $type
     * @param int $userId
     * @return array
     */
    private function getCategoriesChoicesByType(string $type, int $userId): array
    {
        $doctrine = $this->managerRegistry;
        return array_merge(
            ...
            array_map(
                fn($cat) => [$cat->getName() => $cat->getId()],
                $doctrine->getRepository(Category::class)->findBy(
                    ['type' => $type, 'userId' => $userId]
                )
            )
        );
    }
}
