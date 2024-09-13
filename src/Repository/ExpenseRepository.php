<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 *
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    /**
     * Prepare expenses array by date.
     *
     * @param int $userId
     * @param string $date
     * @return array
     */
    public function prepareExpensesByDate(int $userId, string $date): array
    {
        $doctrine = $this->getEntityManager();

        return array_map(
            function ($expense) use ($doctrine) {
                return [
                    'id' => $expense->getId(),
                    'date' => $expense->getDate(),
                    'type' => $expense->getType(),
                    'description' => $expense->getDescription(),
                    'categoryId' => $doctrine->getRepository(Category::class)->find(
                        $expense->getCategoryId()
                    )->getName(),
                    'spending' => $expense->getSpending(),
                ];
            },
            $doctrine->getRepository(Expense::class)->findBy(['userId' => $userId, 'date' => $date])
        );
    }

    /**
     * Prepare expenses array by category id.
     *
     * @param int $userId
     * @param string $type
     * @param int $categoryId
     * @return array
     */
    public function prepareExpensesByCategoryId(int $userId, string $type, int $categoryId): array
    {
        $doctrine = $this->getEntityManager();

        return array_map(
            function ($expense) use ($doctrine) {
                return [
                    'id' => $expense->getId(),
                    'date' => $expense->getDate(),
                    'type' => $expense->getType(),
                    'description' => $expense->getDescription(),
                    'categoryId' => $doctrine->getRepository(Category::class)->find(
                        $expense->getCategoryId()
                    )->getName(),
                    'spending' => $expense->getSpending(),
                ];
            },
            $doctrine->getRepository(Expense::class)->findBy(['userId' => $userId, 'type' => $type, 'categoryId' => $categoryId], ['date' => 'DESC'])
        );
    }

    /**
     * Handy function to prepare expenses inside the array_map function.
     * Currently unused.
     *
     * @param Expense $expense
     * @return array
     */
    private static function prepareExpense(Expense $expense): array
    {
        return
            [
                'id' => $expense->getId(),
                'date' => $expense->getDate(),
                'type' => $expense->getType(),
                'description' => $expense->getDescription(),
                'categoryId' => $expense->getCategoryId(),
                'spending' => $expense->getSpending(),
            ];
    }
}
