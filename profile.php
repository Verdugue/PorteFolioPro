<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Skill.php';
require_once 'classes/Project.php';

$auth = new Auth($pdo);
$skill = new Skill($pdo);
$project = new Project($pdo);

// Vérifier si l'utilisateur est connecté
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Mon Profil';
require_once 'includes/header.php';

// Récupérer les compétences de l'utilisateur
$userSkills = $skill->getUserSkills($_SESSION['user_id']);
// Récupérer les projets de l'utilisateur
$projects = $project->getUserProjects($_SESSION['user_id']);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Mon Profil</h1>

            <!-- Section Compétences -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Mes Compétences</h2>
                    <a href="skills.php" class="btn btn-primary btn-sm">Gérer mes compétences</a>
                </div>
                <div class="card-body">
                    <?php if (empty($userSkills)): ?>
                        <p class="text-muted">Vous n'avez pas encore ajouté de compétences.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($userSkills as $skill): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($skill['name']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars($skill['description']); ?></p>
                                            <div class="progress">
                                                <?php
                                                $levelPercentage = match($skill['level']) {
                                                    'debutant' => 25,
                                                    'intermediaire' => 50,
                                                    'avance' => 75,
                                                    'expert' => 100,
                                                    default => 0
                                                };
                                                $levelClass = match($skill['level']) {
                                                    'debutant' => 'bg-info',
                                                    'intermediaire' => 'bg-success',
                                                    'avance' => 'bg-primary',
                                                    'expert' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <div class="progress-bar <?php echo $levelClass; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $levelPercentage; ?>%"
                                                     aria-valuenow="<?php echo $levelPercentage; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo ucfirst($skill['level']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section Projets -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Mes Projets</h2>
                    <?php if ($auth->isAdmin()): ?>
                        <a href="admin/projects.php" class="btn btn-primary btn-sm">Gérer les projets</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($projects)): ?>
                        <p class="text-muted">Vous n'avez pas encore de projets.</p>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($projects as $project): ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <?php if ($project['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($project['image_path']); ?>" 
                                                 class="card-img-top" 
                                                 alt="<?php echo htmlspecialchars($project['title']); ?>"
                                                 style="height: 200px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars($project['description']); ?></p>
                                            <?php if ($project['external_link']): ?>
                                                <a href="<?php echo htmlspecialchars($project['external_link']); ?>" 
                                                   class="btn btn-primary" 
                                                   target="_blank">
                                                    Voir le projet
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer text-muted">
                                            Ajouté le <?php echo date('d/m/Y', strtotime($project['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 