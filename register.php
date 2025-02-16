<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth($pdo);

// Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->register([
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? ''
    ]);

    if ($result['success']) {
        $success = true;
    } else {
        $errors = $result['errors'];
    }
}

$pageTitle = 'Inscription';
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card auth-card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x mb-3" style="background: linear-gradient(45deg, var(--secondary-color), var(--accent-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <h2 class="auth-title">Créer un compte</h2>
                        <p class="text-muted">Rejoignez-nous pour partager vos projets</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Inscription réussie ! <a href="login.php" class="alert-link">Connectez-vous</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!$success): ?>
                        <form method="POST" action="" novalidate class="needs-validation">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Nom d'utilisateur"
                                       required minlength="3" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                <label for="username">
                                    <i class="fas fa-user me-2"></i>Nom d'utilisateur
                                </label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Email"
                                       required 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Mot de passe"
                                       required minlength="8">
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>Mot de passe
                                </label>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Le mot de passe doit contenir au moins 8 caractères
                                </div>
                            </div>

                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="password_confirm" 
                                       name="password_confirm" 
                                       placeholder="Confirmer le mot de passe"
                                       required>
                                <label for="password_confirm">
                                    <i class="fas fa-lock me-2"></i>Confirmer le mot de passe
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>S'inscrire
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <p class="mb-0">Déjà inscrit ? 
                            <a href="login.php" class="auth-links">
                                <i class="fas fa-sign-in-alt me-1"></i>Connectez-vous
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Fonctionnalités -->
            <div class="row mt-4 g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-project-diagram fa-2x mb-2" style="color: var(--secondary-color);"></i>
                        <h5>Gérez vos projets</h5>
                        <p class="text-muted small">Créez et partagez vos projets facilement</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-tools fa-2x mb-2" style="color: var(--accent-color);"></i>
                        <h5>Compétences</h5>
                        <p class="text-muted small">Mettez en avant vos compétences</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-comments fa-2x mb-2" style="color: #2ecc71;"></i>
                        <h5>Interactions</h5>
                        <p class="text-muted small">Échangez avec la communauté</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Animation des champs de formulaire
document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
    });
    input.addEventListener('blur', function() {
        if (!this.value) {
            this.parentElement.classList.remove('focused');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 