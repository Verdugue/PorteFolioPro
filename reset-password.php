<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth($pdo);

// Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if (empty($token)) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $result = $auth->resetPassword($token, $password);
        
        if ($result['success']) {
            $success = true;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Réinitialisation du mot de passe</h2>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Votre mot de passe a été réinitialisé avec succès.
                                <br>
                                <a href="login.php">Connectez-vous</a> avec votre nouveau mot de passe.
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required minlength="8">
                                    <div class="form-text">
                                        Le mot de passe doit contenir au moins 8 caractères
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirm" class="form-label">
                                        Confirmer le nouveau mot de passe
                                    </label>
                                    <input type="password" class="form-control" id="password_confirm" 
                                           name="password_confirm" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        Réinitialiser le mot de passe
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 