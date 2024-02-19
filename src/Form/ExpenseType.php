<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Expense;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

class ExpenseType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $doctrine = $this->managerRegistry;
        $user = $this->security->getUser();

        if (!$user) {
            throw new \LogicException(
                'The FriendMessageFormType cannot be used without an authenticated user!'
            );
        }

        $userId = $user->getId();
        $choices = array_merge(
            ...
            array_map(fn($cat) => [$cat->getName() => $cat->getName()],
                $doctrine->getRepository(Category::class)->findBy(['type' => 'daily', 'userId' => $userId]))
        );

        $builder
            ->add('date', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Daily' => 'daily',
                    'Major' => 'major',
                    'Home' => 'home',
                ]
            ])
            ->add('category', ChoiceType::class, [
                //'class' => Category::class,
                //'placeholder' => '',
                'choices' => $choices,
            ]);

//        $formModifier = function (FormInterface $form, $type = 'daily') use ($doctrine, $userId): void {
//            $categories = null === $type ? [] : $doctrine->getRepository(Expense::class)->findBy(['type' => $type, 'userId' => $userId]);
//
//            $form->add('category', EntityType::class, [
//                'class' => Category::class,
//                'placeholder' => '',
//                'choices' => $doctrine->getRepository(Expense::class)->findBy(['type' => 'daily', 'userId' => $userId]),
//            ]);
//        };

//        $builder->addEventListener(
//            FormEvents::POST_SET_DATA,
//            function (FormEvent $event) use ($formModifier): void {
//                $data = $event->getData();
//
//                $formModifier($event->getForm(), $data->getType() ?? 'daily');
//            }
//        );
//
//        $builder->get('type')->addEventListener(
//            FormEvents::POST_SET_DATA,
//            function (FormEvent $event) use ($formModifier): void {
//                $type = $event->getForm()->getData();
//
//                $formModifier($event->getForm()->getParent(), $type ?? 'daily');
//            }
//        );

        $builder
            ->add('description', TextType::class)
            ->add('spending', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Add Expense']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
        ]);
    }
}
