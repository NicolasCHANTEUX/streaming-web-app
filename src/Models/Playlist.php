<?php

namespace App\Models;

use App\Core\Database;

class Playlist
{
    public function getAll(): array
    {
        $stmt = Database::query("SELECT * FROM playlists ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?object
    {
        $stmt = Database::query("SELECT * FROM playlists WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(string $name, ?string $description = null): int
    {
        $stmt = Database::query(
            "INSERT INTO playlists (name, description, created_at) VALUES (?, ?, NOW())",
            [$name, $description]
        );

        return (int) Database::connect()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE playlists SET " . implode(', ', $fields) . " WHERE id = ?";
        Database::query($sql, $values);

        return true;
    }

    public function delete(int $id): bool
    {
        // Delete playlist songs first
        Database::query("DELETE FROM playlist_songs WHERE playlist_id = ?", [$id]);
        
        // Delete playlist
        Database::query("DELETE FROM playlists WHERE id = ?", [$id]);
        
        return true;
    }

    public function getSongs(int $playlistId): array
    {
        $stmt = Database::query(
            "SELECT s.*, ps.position 
             FROM songs s
             INNER JOIN playlist_songs ps ON s.id = ps.song_id
             WHERE ps.playlist_id = ?
             ORDER BY ps.position ASC",
            [$playlistId]
        );

        return $stmt->fetchAll();
    }

    public function addSong(int $playlistId, int $songId): bool
    {
        // Get max position
        $stmt = Database::query(
            "SELECT MAX(position) as max_pos FROM playlist_songs WHERE playlist_id = ?",
            [$playlistId]
        );
        
        $result = $stmt->fetch();
        $position = ($result->max_pos ?? 0) + 1;

        Database::query(
            "INSERT INTO playlist_songs (playlist_id, song_id, position) VALUES (?, ?, ?)",
            [$playlistId, $songId, $position]
        );

        return true;
    }

    public function removeSong(int $playlistId, int $songId): bool
    {
        Database::query(
            "DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?",
            [$playlistId, $songId]
        );

        // Reorder remaining songs
        $this->reorderSongs($playlistId);

        return true;
    }

    public function reorderSongs(int $playlistId): void
    {
        $songs = Database::query(
            "SELECT id, song_id FROM playlist_songs WHERE playlist_id = ? ORDER BY position ASC",
            [$playlistId]
        )->fetchAll();

        $position = 1;
        foreach ($songs as $song) {
            Database::query(
                "UPDATE playlist_songs SET position = ? WHERE id = ?",
                [$position, $song->id]
            );
            $position++;
        }
    }
}
