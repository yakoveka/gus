<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public static function getDefaultCategories(): array
    {
        return [
            'daily' => [
                [
                    'name' => 'Groceries',
                    'description' => 'Purchases in supermarkets, shops etc',
                ],
                [
                    'name' => 'Outside lunch',
                    'description' => 'Lunches, restaurants etc not at home',
                ],
                [
                    'name' => 'Transport',
                    'description' => 'Owned vehicle, taxi, public transport',
                ],
                [
                    'name' => 'Gifts',
                    'description' => 'Any gifts',
                ],
                [
                    'name' => 'Health',
                    'description' => 'Health services, pharmacies etc',
                ],
                [
                    'name' => 'Clothes',
                    'description' => 'Daily clothes, care, clothes cleaning etc',
                ],
                [
                    'name' => 'Entertainment',
                    'description' => 'Cinema, theaters, museums',
                ],
                [
                    'name' => 'Regular',
                    'description' => 'Subscriptions on streamings, mobile services, internet etc',
                ],
                [
                    'name' => 'Delivery',
                    'description' => 'Ready food deliveries',
                ],
                [
                    'name' => 'Bad habits',
                    'description' => 'Smoking, drinking etc',
                ],
                [
                    'name' => 'Hobby',
                    'description' => 'Sports, hobbies',
                ],
                [
                    'name' => 'Pets',
                    'description' => 'Pets care, food etc',
                ],
                [
                    'name' => 'Other',
                    'description' => 'Other expenses',
                ],
            ],
            'home' => [
                [
                    'name' => 'Renovation',
                    'description' => 'Expenses related to improving or upgrading your living space',
                ],
                [
                    'name' => 'Mortgage',
                    'description' => 'Payments towards owning your home',
                ],
                [
                    'name' => 'Housing and Communal Services',
                    'description' => 'Costs for utilities, maintenance, and shared amenities in your residence',
                ],
                [
                    'name' => 'Everything for Home',
                    'description' => 'Expenditures on household items, furnishings, and decor',
                ],
                [
                    'name' => 'Other',
                    'description' => 'Miscellaneous expenses not covered by the specified categories',
                ],
            ],
            'major' => [
                [
                    'name' => 'Travel',
                    'description' => 'Costs associated with transportation, accommodation, and activities during trips',
                ],
                [
                    'name' => 'Clothes',
                    'description' => 'Expenditures on attire and accessories for personal wear',
                ],
                [
                    'name' => 'Gadgets',
                    'description' => 'Spending on electronic devices and technological gadgets',
                ],
                [
                    'name' => 'Education',
                    'description' => 'Expenses related to learning, such as tuition, books, and educational materials',
                ],
                [
                    'name' => 'Health',
                    'description' => 'Costs for medical services, treatments, and wellness products',
                ],
                [
                    'name' => 'Other',
                    'description' => 'Miscellaneous expenses not covered by the specified categories',
                ],
            ],
        ];
    }

    public static function prepareCategories(ManagerRegistry $doctrine, string $type, int $userId): array
    {
        return array_map(
            fn($cat) => [
                'date' => $cat->getCategory(),
                'type' => $cat->getType(),
                'description' => $cat->getDescription(),
                'category' => $cat->getCategory(),
                'spending' => $cat->getSpending(),
            ],
            $doctrine->getRepository(Expense::class)->findBy(['type' => $type, 'userId' => $userId])
        );
    }

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
