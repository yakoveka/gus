<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Expense;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ExpenseType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new \LogicException(
                'The FriendMessageFormType cannot be used without an authenticated user!'
            );
        }

        $userId = $user->getId();
        $choices = $this->getCategoriesChoicesByType('daily', $userId);

        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'input' => 'string',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Daily' => 'daily',
                    'Major' => 'major',
                    'Home' => 'home',
                ]
            ])
            ->add('categoryId', ChoiceType::class, ['choices' => $choices, 'constraints' => [], 'mapped' => false])
            ->add('description', TextType::class)
            ->add('spending', NumberType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'float',
                        'message' => 'Invalid category selected.', // Custom error message
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => $options['label']]);

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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
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
        return array_merge(
            ...
            array_map(
                fn($cat) => [$cat->getName() => $cat->getId()],
                $this->managerRegistry->getRepository(Category::class)->findBy(
                    ['type' => $type, 'userId' => $userId]
                )
            )
        );
    }
}
