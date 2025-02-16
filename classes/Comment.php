<?php
class Comment {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupérer les commentaires d'un projet
     */
    public function getProjectComments(int $projectId, bool $onlyApproved = true): array {
        $sql = "
            SELECT c.*, u.username as author
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.project_id = :project_id
        ";
        
        if ($onlyApproved) {
            $sql .= " AND c.status = 'approved'";
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Ajouter un commentaire
     */
    public function addComment(int $projectId, int $userId, string $content): array {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO comments (project_id, user_id, content)
                VALUES (:project_id, :user_id, :content)
            ");
            
            $stmt->execute([
                'project_id' => $projectId,
                'user_id' => $userId,
                'content' => htmlspecialchars($content)
            ]);

            return ['success' => true, 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du commentaire.'];
        }
    }

    /**
     * Supprimer un commentaire
     */
    public function deleteComment(int $id, int $userId = null): array {
        try {
            $sql = "DELETE FROM comments WHERE id = :id";
            $params = ['id' => $id];

            // Si un userId est fourni, vérifier que l'utilisateur est l'auteur du commentaire
            if ($userId !== null) {
                $sql .= " AND user_id = :user_id";
                $params['user_id'] = $userId;
            }

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true];
            }
            return ['success' => false, 'message' => 'Commentaire non trouvé ou permission refusée.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du commentaire.'];
        }
    }

    /**
     * Modérer un commentaire
     */
    public function moderateComment(int $id, string $status): array {
        if (!in_array($status, ['approved', 'rejected'])) {
            return ['success' => false, 'message' => 'Statut de modération invalide.'];
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE comments 
                SET status = :status
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $id,
                'status' => $status
            ]);

            return ['success' => $result];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la modération du commentaire.'];
        }
    }

    /**
     * Récupérer les commentaires en attente de modération
     */
    public function getPendingComments(): array {
        $stmt = $this->pdo->query("
            SELECT c.*, u.username as author, p.title as project_title
            FROM comments c
            JOIN users u ON c.user_id = u.id
            JOIN projects p ON c.project_id = p.id
            WHERE c.status = 'pending'
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Vérifier si un utilisateur peut modifier un commentaire
     */
    public function canModifyComment(int $commentId, int $userId, bool $isAdmin): bool {
        if ($isAdmin) {
            return true;
        }

        $stmt = $this->pdo->prepare("
            SELECT 1 FROM comments 
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute([
            'id' => $commentId,
            'user_id' => $userId
        ]);
        
        return (bool)$stmt->fetch();
    }
} 