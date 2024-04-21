<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Expense;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240420155117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE expense ALTER COLUMN id SET DEFAULT nextval(\'id\')');
        $expenses = [];
        $convertDate = function (string $date): string {
            $tmp = date_create_from_format('d.m.Y', $date);
            return date_format($tmp, 'Y-m-d');
        };
        $userId = 1;
        foreach (['/excel-import/home.csv' => 'home', '/excel-import/major.csv' => 'major'] as $file => $type) {
            if (($handle = fopen(__DIR__ . $file, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $categoryId = $this->connection->fetchOne(
                        "SELECT id FROM category WHERE name = '$data[2]' AND user_id = $userId"
                    );

                    if ($categoryId) {
                        $expense = new Expense();
                        $expense->setDate($convertDate($data[0]));
                        $expense->setDescription($data[1]);
                        $expense->setType($type);
                        $expense->setCategoryId($categoryId);
                        $expense->setSpending(floatval(str_replace(',', '.', $data[3])));
                        $expense->setUserId($userId);
                        $expenses[] = $expense;
                    }
                }
                fclose($handle);
            }
        }

        if (($handle = fopen(__DIR__ . "/excel-import/daily.csv", "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $categoryId = $this->connection->fetchOne(
                    "SELECT id FROM category WHERE name = '$data[1]' AND user_id = $userId"
                );

                if ($categoryId) {
                    $expense = new Expense();
                    $expense->setDate($convertDate($data[0]));
                    $expense->setDescription($data[3]);
                    $expense->setType('daily');
                    $expense->setCategoryId($categoryId);
                    $expense->setSpending(floatval(str_replace(',', '.', $data[2])));
                    $expense->setUserId($userId);
                    $expenses[] = $expense;
                }
            }
            fclose($handle);
        }

        foreach ($expenses as $expense) {
            $this->addSql(
                "INSERT INTO expense (description, spending, currency, date, type, user_id, category_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $expense->getDescription(),
                    $expense->getSpending(),
                    $expense->getCurrency(),
                    $expense->getDate(),
                    $expense->getType(),
                    $expense->getUserId(),
                    $expense->getCategoryId()
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM expense");
    }
}
