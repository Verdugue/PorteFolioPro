<?php
class Category {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupérer toutes les catégories
     */
    public function getAllCategories(): array {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Récupérer une catégorie par son ID
     */
    public function getCategoryById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Ajouter une nouvelle catégorie
     */
    public function addCategory(string $name, string $description = ''): array {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO categories (name, description) VALUES (?, ?)"
            );
            $stmt->execute([$name, $description]);
            return [
                'success' => true,
                'id' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                return [
                    'success' => false,
                    'message' => 'Cette catégorie existe déjà.'
                ];
            }
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la catégorie.'
            ];
        }
    }

    /**
     * Mettre à jour une catégorie
     */
    public function updateCategory(int $id, string $name, string $description = ''): array {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE categories 
                 SET name = ?, description = ? 
                 WHERE id = ?"
            );
            $result = $stmt->execute([$name, $description, $id]);
            return ['success' => $result];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return [
                    'success' => false,
                    'message' => 'Cette catégorie existe déjà.'
                ];
            }
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la catégorie.'
            ];
        }
    }

    /**
     * Supprimer une catégorie
     */
    public function deleteCategory(int $id): array {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
            $result = $stmt->execute([$id]);
            return ['success' => $result];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la catégorie.'
            ];
        }
    }

    /**
     * Récupérer les projets d'une catégorie
     */
    public function getCategoryProjects(int $categoryId): array {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, u.username as author 
             FROM projects p 
             JOIN users u ON p.user_id = u.id 
             WHERE p.category_id = ? 
             ORDER BY p.created_at DESC"
        );
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    /**
     * Compter le nombre de projets dans une catégorie
     */
    public function countCategoryProjects(int $categoryId): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM projects WHERE category_id = ?"
        );
        $stmt->execute([$categoryId]);
        return (int)$stmt->fetchColumn();
    }
} 