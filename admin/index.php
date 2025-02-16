<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth($pdo);

// Vérifier si l'utilisateur est admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Administration';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Panneau d'administration</h1>
            
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <!-- Gestion des projets -->
                <div class="col">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-project-diagram"></i> Gestion des projets
                            </h5>
                            <p class="card-text">
                                Gérez les projets du site : ajout, modification, suppression et organisation des projets.
                            </p>
                            <a href="projects.php" class="btn btn-primary">
                                Gérer les projets
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Gestion des compétences -->
                <div class="col">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-tools"></i> Gestion des compétences
                            </h5>
                            <p class="card-text">
                                Gérez la liste des compétences disponibles sur la plateforme.
                            </p>
                            <a href="skills.php" class="btn btn-primary">
                                Gérer les compétences
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Modération des commentaires -->
                <div class="col">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-comments"></i> Modération des commentaires
                            </h5>
                            <p class="card-text">
                                Modérez les commentaires : approbation, rejet et gestion du contenu.
                            </p>
                            <a href="comments.php" class="btn btn-primary">
                                Modérer les commentaires
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Gestion des catégories -->
                <div class="col">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-folder"></i> Gestion des catégories
                            </h5>
                            <p class="card-text">
                                Gérez les catégories de projets : création, modification et organisation.
                            </p>
                            <a href="categories.php" class="btn btn-primary">
                                Gérer les catégories
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Gestion des tags -->
                <div class="col">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-tags"></i> Gestion des tags
                            </h5>
                            <p class="card-text">
                                Gérez les tags utilisés pour classifier les projets.
                            </p>
                            <a href="tags.php" class="btn btn-primary">
                                Gérer les tags
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="col">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chart-bar"></i> Statistiques
                            </h5>
                            <p class="card-text">
                                Consultez les statistiques du site : utilisateurs, projets, activités.
                            </p>
                            <a href="statistics.php" class="btn btn-primary">
                                Voir les statistiques
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 