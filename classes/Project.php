<?php
class Project {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les projets
     */
    public function getAllProjects() {
        $query = "SELECT p.*, u.username as author_name 
                 FROM projects p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 ORDER BY p.created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les projets d'un utilisateur spécifique
     */
    public function getUserProjects($userId) {
        $query = "SELECT * FROM projects WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un projet par son ID
     */
    public function getProjectById($id) {
        $query = "SELECT p.*, u.username as author_name 
                 FROM projects p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Ajoute un nouveau projet
     */
    public function addProject(array $data, ?array $image = null): array {
        try {
            // Traitement de l'image si elle est fournie
            $imagePath = null;
            if ($image && $image['tmp_name']) {
                $result = $this->handleImageUpload($image);
                if (!$result['success']) {
                    return $result;
                }
                $imagePath = $result['path'];
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO projects (title, description, image_path, external_link, user_id)
                VALUES (:title, :description, :image_path, :external_link, :user_id)
            ");

            $stmt->execute([
                'title' => $data['title'],
                'description' => $data['description'],
                'image_path' => $imagePath,
                'external_link' => $data['external_link'] ?? null,
                'user_id' => $_SESSION['user_id']
            ]);

            return ['success' => true, 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du projet.'];
        }
    }

    /**
     * Met à jour un projet existant
     */
    public function updateProject(int $id, array $data, ?array $image = null): array {
        try {
            $project = $this->getProjectById($id);
            if (!$project) {
                return ['success' => false, 'message' => 'Projet non trouvé.'];
            }

            // Traitement de l'image si elle est fournie
            $imagePath = $project['image_path'];
            if ($image && $image['tmp_name']) {
                $result = $this->handleImageUpload($image);
                if (!$result['success']) {
                    return $result;
                }
                // Supprimer l'ancienne image
                if ($imagePath) {
                    $this->deleteImage($imagePath);
                }
                $imagePath = $result['path'];
            }

            $stmt = $this->pdo->prepare("
                UPDATE projects 
                SET title = :title,
                    description = :description,
                    image_path = :image_path,
                    external_link = :external_link
                WHERE id = :id
            ");

            $result = $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'description' => $data['description'],
                'image_path' => $imagePath,
                'external_link' => $data['external_link'] ?? null
            ]);

            return ['success' => $result];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du projet.'];
        }
    }

    /**
     * Supprime un projet
     */
    public function deleteProject(int $id): array {
        try {
            $project = $this->getProjectById($id);
            if (!$project) {
                return ['success' => false, 'message' => 'Projet non trouvé.'];
            }

            // Supprimer l'image associée
            if ($project['image_path']) {
                $this->deleteImage($project['image_path']);
            }

            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);

            return ['success' => $result];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du projet.'];
        }
    }

    /**
     * Gère l'upload d'une image
     */
    private function handleImageUpload(array $image): array {
        $uploadDir = 'uploads/projects/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => 'Format de fichier non autorisé. Formats acceptés : ' . implode(', ', $allowedExtensions)
            ];
        }

        if ($image['size'] > $maxFileSize) {
            return [
                'success' => false,
                'message' => 'L\'image ne doit pas dépasser 5MB.'
            ];
        }

        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($image['tmp_name'], $filepath)) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'upload de l\'image.'
            ];
        }

        return [
            'success' => true,
            'path' => $filepath
        ];
    }

    /**
     * Supprime une image
     */
    private function deleteImage(string $path): bool {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Vérifie si un utilisateur est le propriétaire d'un projet
     */
    public function isProjectOwner($projectId, $userId): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM projects 
            WHERE id = :project_id AND user_id = :user_id
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $userId
        ]);
        return (bool)$stmt->fetchColumn();
    }
} 