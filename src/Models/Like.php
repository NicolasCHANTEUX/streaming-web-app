<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Like
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function isLiked(int $songId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM liked_songs WHERE song_id = ?");
        $stmt->execute([$songId]);
        return $stmt->fetch() !== false;
    }

    public function like(int $songId): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO liked_songs (song_id) VALUES (?)");
            return $stmt->execute([$songId]);
        } catch (\PDOException $e) {
            // Already liked or song doesn't exist
            return false;
        }
    }

    public function unlike(int $songId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM liked_songs WHERE song_id = ?");
        return $stmt->execute([$songId]);
    }

    public function toggle(int $songId): array
    {
        if ($this->isLiked($songId)) {
            $this->unlike($songId);
            return ['liked' => false];
        } else {
            $this->like($songId);
            return ['liked' => true];
        }
    }

    public function getLikedSongs(): array
    {
        $stmt = $this->db->query("
            SELECT s.* 
            FROM songs s
            INNER JOIN liked_songs l ON s.id = l.song_id
            ORDER BY l.created_at DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getLikedCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM liked_songs");
        return (int) $stmt->fetchColumn();
    }
}
