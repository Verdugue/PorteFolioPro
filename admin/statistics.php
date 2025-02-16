<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth($pdo);

// Vérifier si l'utilisateur est admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Récupérer les statistiques
try {
    // Nombre total d'utilisateurs
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Nombre d'utilisateurs par rôle
    $usersByRole = $pdo->query("
        SELECT role, COUNT(*) as count 
        FROM users 
        GROUP BY role
    ")->fetchAll();

    // Nombre total de projets
    $projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    
    // Projets par catégorie
    $projectsByCategory = $pdo->query("
        SELECT c.name, COUNT(p.id) as count 
        FROM categories c
        LEFT JOIN projects p ON c.id = p.category_id
        GROUP BY c.id, c.name
        ORDER BY count DESC
    ")->fetchAll();

    // Nombre total de commentaires
    $commentCount = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    
    // Commentaires par statut
    $commentsByStatus = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM comments 
        GROUP BY status
    ")->fetchAll();

    // Nombre total de compétences
    $skillCount = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
    
    // Compétences les plus utilisées
    $topSkills = $pdo->query("
        SELECT s.name, COUNT(us.user_id) as count 
        FROM skills s
        LEFT JOIN user_skills us ON s.id = us.skill_id
        GROUP BY s.id, s.name
        ORDER BY count DESC
        LIMIT 5
    ")->fetchAll();

    // Nombre total de tags
    $tagCount = $pdo->query("SELECT COUNT(*) FROM tags")->fetchColumn();
    
    // Tags les plus utilisés
    $topTags = $pdo->query("
        SELECT t.name, COUNT(pt.project_id) as count 
        FROM tags t
        LEFT JOIN project_tags pt ON t.id = pt.tag_id
        GROUP BY t.id, t.name
        ORDER BY count DESC
        LIMIT 5
    ")->fetchAll();

    // Activité récente (derniers projets et commentaires)
    $recentActivity = $pdo->query("
        (SELECT 'project' as type, title as content, created_at, NULL as user_id
         FROM projects
         ORDER BY created_at DESC
         LIMIT 5)
        UNION ALL
        (SELECT 'comment' as type, content, c.created_at, c.user_id
         FROM comments c
         JOIN users u ON c.user_id = u.id
         WHERE status = 'approved'
         ORDER BY created_at DESC
         LIMIT 5)
        ORDER BY created_at DESC
        LIMIT 10
    ")->fetchAll();

} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des statistiques : " . $e->getMessage();
}

$pageTitle = 'Statistiques';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Statistiques du site</h2>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Statistiques générales -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-users me-2"></i>Utilisateurs
                                    </h5>
                                    <h2 class="mb-0"><?php echo $userCount; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-project-diagram me-2"></i>Projets
                                    </h5>
                                    <h2 class="mb-0"><?php echo $projectCount; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-comments me-2"></i>Commentaires
                                    </h5>
                                    <h2 class="mb-0"><?php echo $commentCount; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-tags me-2"></i>Tags
                                    </h5>
                                    <h2 class="mb-0"><?php echo $tagCount; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Projets par catégorie -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-folder me-2"></i>Projets par catégorie
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Catégorie</th>
                                                    <th>Nombre</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($projectsByCategory as $cat): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                                        <td><?php echo $cat['count']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top compétences -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tools me-2"></i>Top 5 des compétences
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Compétence</th>
                                                    <th>Utilisateurs</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($topSkills as $skill): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($skill['name']); ?></td>
                                                        <td><?php echo $skill['count']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Commentaires par statut -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-comment-dots me-2"></i>Commentaires par statut
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Statut</th>
                                                    <th>Nombre</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($commentsByStatus as $status): ?>
                                                    <tr>
                                                        <td><?php echo ucfirst($status['status']); ?></td>
                                                        <td><?php echo $status['count']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top tags -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-hashtag me-2"></i>Top 5 des tags
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Tag</th>
                                                    <th>Utilisations</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($topTags as $tag): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo htmlspecialchars($tag['name']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $tag['count']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activité récente -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history me-2"></i>Activité récente
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Contenu</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentActivity as $activity): ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($activity['type'] === 'project'): ?>
                                                                <i class="fas fa-project-diagram text-primary"></i>
                                                                Nouveau projet
                                                            <?php else: ?>
                                                                <i class="fas fa-comment text-success"></i>
                                                                Nouveau commentaire
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($activity['content']); ?></td>
                                                        <td>
                                                            <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 