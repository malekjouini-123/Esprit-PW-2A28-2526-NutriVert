<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Exercise
{
    private PDO $pdo;
    private array $allowedSortColumns = ['sets', 'reps', 'created_at'];

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT e.*, c.title AS coaching_title
             FROM exercises e
             LEFT JOIN coaching_programs c ON c.id = e.coaching_id
             ORDER BY e.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, c.title AS coaching_title
             FROM exercises e
             LEFT JOIN coaching_programs c ON c.id = e.coaching_id
             WHERE e.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO exercises (coaching_id, name, description, sets, reps, rest_time, image)
             VALUES (:coaching_id, :name, :description, :sets, :reps, :rest_time, :image)'
        );

        return $stmt->execute([
            'coaching_id' => $data['coaching_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'sets' => $data['sets'],
            'reps' => $data['reps'],
            'rest_time' => $data['rest_time'],
            'image' => $data['image'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['image'])) {
            $stmt = $this->pdo->prepare(
                'UPDATE exercises
                 SET coaching_id = :coaching_id, name = :name, description = :description, sets = :sets,
                     reps = :reps, rest_time = :rest_time, image = :image
                 WHERE id = :id'
            );

            return $stmt->execute([
                'id' => $id,
                'coaching_id' => $data['coaching_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'sets' => $data['sets'],
                'reps' => $data['reps'],
                'rest_time' => $data['rest_time'],
                'image' => $data['image'],
            ]);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE exercises
             SET coaching_id = :coaching_id, name = :name, description = :description, sets = :sets,
                 reps = :reps, rest_time = :rest_time
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'coaching_id' => $data['coaching_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'sets' => $data['sets'],
            'reps' => $data['reps'],
            'rest_time' => $data['rest_time'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM exercises WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function search(string $keyword): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, c.title AS coaching_title
             FROM exercises e
             LEFT JOIN coaching_programs c ON c.id = e.coaching_id
             WHERE e.name LIKE :keyword
             ORDER BY e.created_at DESC'
        );
        $stmt->execute(['keyword' => '%' . $keyword . '%']);

        return $stmt->fetchAll();
    }

    public function sort(string $column, string $order): array
    {
        $safeColumn = in_array($column, $this->allowedSortColumns, true) ? $column : 'created_at';
        $safeOrder = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql = "SELECT e.*, c.title AS coaching_title
                FROM exercises e
                LEFT JOIN coaching_programs c ON c.id = e.coaching_id
                ORDER BY e.{$safeColumn} {$safeOrder}";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getByCoaching(int $coachingId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, c.title AS coaching_title
             FROM exercises e
             LEFT JOIN coaching_programs c ON c.id = e.coaching_id
             WHERE e.coaching_id = :coaching_id
             ORDER BY e.created_at DESC'
        );
        $stmt->execute(['coaching_id' => $coachingId]);

        return $stmt->fetchAll();
    }
}
