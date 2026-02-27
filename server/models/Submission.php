<?php
/**
 * Submission.php
 * Model for the `submissions` (contact form) table.
 */

require_once __DIR__ . '/../config/database.php';

class Submission
{
    public static function create(array $data): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO submissions (name, email, message) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$data['name'], $data['email'], $data['message']]);
    }

    public static function findAll(): array
    {
        $pdo  = getDB();
        $stmt = $pdo->query('SELECT * FROM submissions ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM submissions WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function delete(int $id): bool
    {
        $pdo  = getDB();
        $stmt = $pdo->prepare('DELETE FROM submissions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function count(): int
    {
        $pdo  = getDB();
        $stmt = $pdo->query('SELECT COUNT(*) FROM submissions');
        return (int)$stmt->fetchColumn();
    }
}
