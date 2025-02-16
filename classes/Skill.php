<?php
class Skill {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupérer toutes les compétences
     */
    public function getAllSkills(): array {
        $stmt = $this->pdo->query("SELECT * FROM skills ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Récupérer une compétence par son ID
     */
    public function getSkillById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM skills WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Ajouter une nouvelle compétence
     */
    public function addSkill(string $name, string $description = ''): array {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO skills (name, description) VALUES (?, ?)"
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
                    'message' => 'Cette compétence existe déjà.'
                ];
            }
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la compétence.'
            ];
        }
    }

    /**
     * Mettre à jour une compétence
     */
    public function updateSkill(int $id, string $name, string $description = ''): array {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE skills 
                 SET name = ?, description = ? 
                 WHERE id = ?"
            );
            $result = $stmt->execute([$name, $description, $id]);
            return ['success' => $result];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return [
                    'success' => false,
                    'message' => 'Cette compétence existe déjà.'
                ];
            }
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la compétence.'
            ];
        }
    }

    /**
     * Supprimer une compétence
     */
    public function deleteSkill(int $id): array {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM skills WHERE id = ?");
            $result = $stmt->execute([$id]);
            return ['success' => $result];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la compétence.'
            ];
        }
    }

    /**
     * Récupérer les compétences d'un utilisateur
     */
    public function getUserSkills(int $userId): array {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, us.level 
             FROM skills s
             JOIN user_skills us ON s.id = us.skill_id
             WHERE us.user_id = ?
             ORDER BY s.name"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Ajouter ou mettre à jour une compétence utilisateur
     */
    public function updateUserSkill(int $userId, int $skillId, string $level): array {
        if (!in_array($level, ['debutant', 'intermediaire', 'avance', 'expert'])) {
            return [
                'success' => false,
                'message' => 'Niveau de compétence invalide.'
            ];
        }

        try {
            // Vérifier si la compétence existe
            $stmt = $this->pdo->prepare("SELECT 1 FROM skills WHERE id = ?");
            $stmt->execute([$skillId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Compétence non trouvée.'
                ];
            }

            // Vérifier si l'utilisateur a déjà cette compétence
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM user_skills 
                 WHERE user_id = ? AND skill_id = ?"
            );
            $stmt->execute([$userId, $skillId]);
            
            if ($stmt->fetch()) {
                // Mettre à jour le niveau
                $stmt = $this->pdo->prepare(
                    "UPDATE user_skills 
                     SET level = ? 
                     WHERE user_id = ? AND skill_id = ?"
                );
                $stmt->execute([$level, $userId, $skillId]);
            } else {
                // Ajouter la nouvelle compétence
                $stmt = $this->pdo->prepare(
                    "INSERT INTO user_skills (user_id, skill_id, level) 
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([$userId, $skillId, $level]);
            }

            return ['success' => true];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la compétence.'
            ];
        }
    }

    /**
     * Supprimer une compétence utilisateur
     */
    public function removeUserSkill(int $userId, int $skillId): array {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM user_skills 
                 WHERE user_id = ? AND skill_id = ?"
            );
            $result = $stmt->execute([$userId, $skillId]);
            return ['success' => $result];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la compétence.'
            ];
        }
    }
} 