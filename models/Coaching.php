<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Coaching
{
    private PDO $pdo;
    private array $allowedSortColumns = ['duration_weeks', 'difficulty_level', 'created_at'];

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM coaching_programs ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM coaching_programs WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO coaching_programs (title, description, duration_weeks, difficulty_level)
             VALUES (:title, :description, :duration_weeks, :difficulty_level)'
        );

        return $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'duration_weeks' => $data['duration_weeks'],
            'difficulty_level' => $data['difficulty_level'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE coaching_programs
             SET title = :title, description = :description, duration_weeks = :duration_weeks, difficulty_level = :difficulty_level
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'duration_weeks' => $data['duration_weeks'],
            'difficulty_level' => $data['difficulty_level'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM coaching_programs WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function search(string $keyword): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM coaching_programs
             WHERE title LIKE :keyword
             ORDER BY created_at DESC'
        );
        $stmt->execute(['keyword' => '%' . $keyword . '%']);

        return $stmt->fetchAll();
    }

    public function sort(string $column, string $order): array
    {
        $safeColumn = in_array($column, $this->allowedSortColumns, true) ? $column : 'created_at';
        $safeOrder = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        if ($safeColumn === 'difficulty_level') {
            $sql = "SELECT * FROM coaching_programs
                    ORDER BY FIELD(difficulty_level, 'easy', 'medium', 'hard') {$safeOrder}, created_at DESC";
        } else {
            $sql = "SELECT * FROM coaching_programs ORDER BY {$safeColumn} {$safeOrder}";
        }

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
