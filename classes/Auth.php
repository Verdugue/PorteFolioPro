<?php
class Auth {
    private $pdo;
    private $sessionTokenKey = 'user_token';
    private $rememberTokenKey = 'remember_token';
    private $csrfTokenKey = 'csrf_token';

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register($data) {
        $errors = [];

        // Validation des données
        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }

        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        // Vérifier si l'email existe déjà
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            $errors[] = "Cette adresse email est déjà utilisée.";
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        // Hashage du mot de passe
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insertion dans la base de données
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, email, password, role, created_at) 
             VALUES (?, ?, ?, 'user', NOW())"
        );

        try {
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword
            ]);

            return [
                'success' => true
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'errors' => ["Une erreur est survenue lors de l'inscription."]
            ];
        }
    }

    public function login($email, $password, $remember = false) {
        $stmt = $this->pdo->prepare(
            "SELECT id, username, password, role 
             FROM users 
             WHERE email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Générer un nouveau token de session
            $sessionToken = bin2hex(random_bytes(32));
            $_SESSION[$this->sessionTokenKey] = $sessionToken;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = ($user['role'] === 'admin');

            // Si "Se souvenir de moi" est coché
            if ($remember) {
                $rememberToken = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

                $stmt = $this->pdo->prepare(
                    "INSERT INTO remember_tokens (user_id, token, expiry) 
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([$user['id'], $rememberToken, $expiry]);

                // Définir le cookie
                setcookie(
                    $this->rememberTokenKey,
                    $rememberToken,
                    strtotime('+30 days'),
                    '/',
                    '',
                    true,
                    true
                );
            }

            return [
                'success' => true
            ];
        }

        return [
            'success' => false,
            'message' => "Email ou mot de passe incorrect."
        ];
    }

    public function logout() {
        // Supprimer le token "Se souvenir de moi"
        if (isset($_COOKIE[$this->rememberTokenKey])) {
            $stmt = $this->pdo->prepare(
                "DELETE FROM remember_tokens 
                 WHERE token = ?"
            );
            $stmt->execute([$_COOKIE[$this->rememberTokenKey]]);
            
            setcookie(
                $this->rememberTokenKey,
                '',
                time() - 3600,
                '/',
                '',
                true,
                true
            );
        }

        // Supprimer les variables de session
        unset($_SESSION[$this->sessionTokenKey]);
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['is_admin']);
        session_destroy();
    }

    public function isLoggedIn() {
        // Vérifier si l'utilisateur est connecté via la session
        if (isset($_SESSION[$this->sessionTokenKey]) && isset($_SESSION['user_id'])) {
            return true;
        }

        // Vérifier le token "Se souvenir de moi"
        if (isset($_COOKIE[$this->rememberTokenKey])) {
            $stmt = $this->pdo->prepare(
                "SELECT user_id 
                 FROM remember_tokens 
                 WHERE token = ? AND expiry > NOW()"
            );
            $stmt->execute([$_COOKIE[$this->rememberTokenKey]]);
            $result = $stmt->fetch();

            if ($result) {
                // Récupérer les informations de l'utilisateur
                $stmt = $this->pdo->prepare(
                    "SELECT id, username, role 
                     FROM users 
                     WHERE id = ?"
                );
                $stmt->execute([$result['user_id']]);
                $user = $stmt->fetch();

                if ($user) {
                    // Régénérer la session
                    $_SESSION[$this->sessionTokenKey] = bin2hex(random_bytes(32));
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = ($user['role'] === 'admin');
                    return true;
                }
            }

            // Si le token est invalide ou expiré, le supprimer
            setcookie(
                $this->rememberTokenKey,
                '',
                time() - 3600,
                '/',
                '',
                true,
                true
            );
        }

        return false;
    }

    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    public function generateCsrfToken() {
        if (!isset($_SESSION[$this->csrfTokenKey])) {
            $_SESSION[$this->csrfTokenKey] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$this->csrfTokenKey];
    }

    public function verifyCsrfToken($token) {
        return isset($_SESSION[$this->csrfTokenKey]) && 
               hash_equals($_SESSION[$this->csrfTokenKey], $token);
    }

    public function initiatePasswordReset($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => "Aucun compte n'est associé à cette adresse email."
            ];
        }

        // Générer un token de réinitialisation
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        try {
            $this->pdo->beginTransaction();

            // Supprimer les anciens tokens non utilisés
            $stmt = $this->pdo->prepare(
                "DELETE FROM password_resets 
                 WHERE user_id = ? AND used = 0"
            );
            $stmt->execute([$user['id']]);

            // Insérer le nouveau token
            $stmt = $this->pdo->prepare(
                "INSERT INTO password_resets (user_id, token, expiry) 
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$user['id'], $token, $expiry]);

            // Envoyer l'email
            require_once 'Mailer.php';
            $mailer = new Mailer();
            $emailResult = $mailer->sendPasswordReset($email, $token);

            if (!$emailResult['success']) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => "Erreur lors de l'envoi de l'email : " . $emailResult['message']
                ];
            }

            $this->pdo->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => "Une erreur est survenue lors de la réinitialisation."
            ];
        }
    }

    public function resetPassword($token, $newPassword) {
        $stmt = $this->pdo->prepare(
            "SELECT user_id 
             FROM password_resets 
             WHERE token = ? AND expiry > NOW() AND used = 0"
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            return [
                'success' => false,
                'message' => "Le lien de réinitialisation est invalide ou a expiré."
            ];
        }

        // Hasher le nouveau mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Mettre à jour le mot de passe
        $stmt = $this->pdo->prepare(
            "UPDATE users 
             SET password = ? 
             WHERE id = ?"
        );
        $stmt->execute([$hashedPassword, $reset['user_id']]);

        // Marquer le token comme utilisé
        $stmt = $this->pdo->prepare(
            "UPDATE password_resets 
             SET used = 1 
             WHERE token = ?"
        );
        $stmt->execute([$token]);

        return [
            'success' => true
        ];
    }
} 