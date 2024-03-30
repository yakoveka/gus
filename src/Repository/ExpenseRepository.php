<?php

namespace App\Repository;

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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public static function prepareExpensesByDate(
        ManagerRegistry $doctrine,
        string $type,
        int $userId,
        string $date
    ): array {
        return array_map(
            self::prepareExpense(...),
            $doctrine->getRepository(Expense::class)->findBy(['type' => $type, 'userId' => $userId, 'date' => $date])
        );
    }

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
