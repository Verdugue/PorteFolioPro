<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth($pdo);
$pageTitle = 'Accueil';

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card auth-card">
            <div class="card-body">
                <?php if ($auth->isLoggedIn()): ?>
                    <h2 class="auth-title">Bienvenue sur votre espace personnel</h2>
                    
                    <?php if ($auth->isAdmin()): ?>
                        <div class="alert alert-info">
                            Vous êtes connecté en tant qu'administrateur.
                        </div>
                    <?php endif; ?>

                    <p class="lead">
                        Vous êtes maintenant connecté et pouvez accéder à toutes les fonctionnalités de votre compte.
                    </p>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Mon Profil</h5>
                                    <p class="card-text">Gérez vos informations personnelles et vos préférences.</p>
                                    <a href="profile.php" class="btn btn-primary">Modifier mon profil</a>
                                </div>
                            </div>
                        </div>
                        <?php if ($auth->isAdmin()): ?>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Administration</h5>
                                        <p class="card-text">Gérez les utilisateurs et les paramètres du site.</p>
                                        <a href="admin/" class="btn btn-primary">Accéder à l'administration</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <h2 class="auth-title">Bienvenue sur notre site</h2>
                    <p class="lead">
                        Pour accéder à toutes les fonctionnalités, veuillez vous connecter ou créer un compte.
                    </p>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Connexion</h5>
                                    <p class="card-text">Déjà membre ? Connectez-vous à votre compte.</p>
                                    <a href="login.php" class="btn btn-primary">Se connecter</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Inscription</h5>
                                    <p class="card-text">Nouveau ? Créez votre compte gratuitement.</p>
                                    <a href="register.php" class="btn btn-primary">S'inscrire</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>