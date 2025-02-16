<?php
class Tag {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupérer tous les tags
     */
    public function getAllTags(): array {
        $stmt = $this->pdo->query("SELECT * FROM tags ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Récupérer un tag par son ID
     */
    public function getTagById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Ajouter un nouveau tag
     */
    public function addTag(string $name): array {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO tags (name) VALUES (:name)");
            $stmt->execute(['name' => htmlspecialchars($name)]);
            return ['success' => true, 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                return ['success' => false, 'message' => 'Ce tag existe déjà.'];
            }
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du tag.'];
        }
    }

    /**
     * Supprimer un tag
     */
    public function deleteTag(int $id): array {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM tags WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);
            return ['success' => $result];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du tag.'];
        }
    }

    /**
     * Récupérer les tags d'un projet
     */
    public function getProjectTags(int $projectId): array {
        $stmt = $this->pdo->prepare("
            SELECT t.*
            FROM tags t
            JOIN project_tags pt ON t.id = pt.tag_id
            WHERE pt.project_id = :project_id
            ORDER BY t.name
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Ajouter des tags à un projet
     */
    public function addProjectTags(int $projectId, array $tagIds): array {
        try {
            $this->pdo->beginTransaction();

            // Supprimer les anciens tags
            $stmt = $this->pdo->prepare("
                DELETE FROM project_tags WHERE project_id = :project_id
            ");
            $stmt->execute(['project_id' => $projectId]);

            // Ajouter les nouveaux tags
            $stmt = $this->pdo->prepare("
                INSERT INTO project_tags (project_id, tag_id) VALUES (:project_id, :tag_id)
            ");
            foreach ($tagIds as $tagId) {
                $stmt->execute([
                    'project_id' => $projectId,
                    'tag_id' => $tagId
                ]);
            }

            $this->pdo->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout des tags au projet.'];
        }
    }

    /**
     * Rechercher des projets par tag
     */
    public function searchProjectsByTags(array $tagIds): array {
        if (empty($tagIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($tagIds) - 1) . '?';
        $sql = "
            SELECT p.*, u.username as author, COUNT(DISTINCT pt.tag_id) as tag_matches
            FROM projects p
            JOIN users u ON p.user_id = u.id
            JOIN project_tags pt ON p.id = pt.project_id
            WHERE pt.tag_id IN ($placeholders)
            GROUP BY p.id
            HAVING tag_matches = ?
            ORDER BY p.created_at DESC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $params = array_merge($tagIds, [count($tagIds)]);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
} 