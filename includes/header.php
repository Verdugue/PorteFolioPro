<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - DevShowcase' : 'DevShowcase'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <?php
    // Détecter si nous sommes dans l'administration
    $isAdmin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    $cssPath = $isAdmin ? '../public/css/style.css' : 'public/css/style.css';
    ?>
    <link href="<?php echo $cssPath; ?>" rel="stylesheet">
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $isAdmin ? '../index.php' : 'index.php'; ?>">DevShowcase</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $isAdmin ? '../projects.php' : 'projects.php'; ?>">
                            <i class="fas fa-project-diagram me-1"></i>Projets
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $isAdmin ? '../my-projects.php' : 'my-projects.php'; ?>">
                                <i class="fas fa-folder me-1"></i>Mes Projets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $isAdmin ? '../skills.php' : 'skills.php'; ?>">
                                <i class="fas fa-tools me-1"></i>Compétences
                            </a>
                        </li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $isAdmin ? './' : 'admin/'; ?>">
                                    <i class="fas fa-cog me-1"></i>Administration
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $isAdmin ? '../profile.php' : 'profile.php'; ?>">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $isAdmin ? '../logout.php' : 'logout.php'; ?>">
                                <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $isAdmin ? '../login.php' : 'login.php'; ?>">
                                <i class="fas fa-sign-in-alt me-1"></i>Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $isAdmin ? '../register.php' : 'register.php'; ?>">
                                <i class="fas fa-user-plus me-1"></i>Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container"><?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?> 