<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Project.php';
require_once 'classes/Category.php';
require_once 'classes/Tag.php';
require_once 'classes/Comment.php';

$auth = new Auth($pdo);
$project = new Project($pdo);
$category = new Category($pdo);
$tag = new Tag($pdo);
$comment = new Comment($pdo);

$pageTitle = 'Projets';
require_once 'includes/header.php';

// Récupérer les filtres
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;
$selectedTags = isset($_GET['tags']) ? array_map('intval', (array)$_GET['tags']) : [];

// Récupérer les projets selon les filtres
if (!empty($selectedTags)) {
    $projects = $tag->searchProjectsByTags($selectedTags);
} elseif ($selectedCategory) {
    $projects = $category->getCategoryProjects($selectedCategory);
} else {
    $projects = $project->getAllProjects();
}

// Récupérer toutes les catégories et tags pour les filtres
$categories = $category->getAllCategories();
$allTags = $tag->getAllTags();

// Traitement de l'ajout de commentaire
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $auth->isLoggedIn()) {
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Session expirée. Veuillez réessayer.';
    } else if (isset($_POST['project_id'], $_POST['content'])) {
        $result = $comment->addComment(
            (int)$_POST['project_id'],
            $_SESSION['user_id'],
            $_POST['content']
        );
        if ($result['success']) {
            $message = 'Commentaire ajouté avec succès. En attente de modération.';
        } else {
            $error = $result['message'];
        }
    }
}

// Générer un nouveau token CSRF
$csrfToken = $auth->generateCsrfToken();
?>

<div class="container mt-4">
    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Filtres</h5>
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Catégorie</label>
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $selectedCategory == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Tags</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($allTags as $t): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="tags[]" value="<?php echo $t['id']; ?>"
                                               id="tag<?php echo $t['id']; ?>"
                                               <?php echo in_array($t['id'], $selectedTags) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tag<?php echo $t['id']; ?>">
                                            <?php echo htmlspecialchars($t['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Filtrer</button>
                            <a href="projects.php" class="btn btn-secondary">Réinitialiser</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Liste des projets -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($projects as $p): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if ($p['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($p['image_path']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($p['title']); ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($p['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($p['description']); ?></p>
                        
                        <!-- Tags du projet -->
                        <?php $projectTags = $tag->getProjectTags($p['id']); ?>
                        <?php if (!empty($projectTags)): ?>
                            <div class="mb-3">
                                <?php foreach ($projectTags as $t): ?>
                                    <span class="badge bg-secondary me-1">
                                        <?php echo htmlspecialchars($t['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($p['external_link']): ?>
                            <a href="<?php echo htmlspecialchars($p['external_link']); ?>" 
                               class="btn btn-primary mb-2" 
                               target="_blank">
                                Voir le projet
                            </a>
                        <?php endif; ?>

                        <!-- Commentaires -->
                        <?php $projectComments = $comment->getProjectComments($p['id']); ?>
                        <button class="btn btn-outline-primary" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#comments<?php echo $p['id']; ?>">
                            Commentaires (<?php echo count($projectComments); ?>)
                        </button>

                        <div class="collapse mt-3" id="comments<?php echo $p['id']; ?>">
                            <?php foreach ($projectComments as $c): ?>
                                <div class="card card-body mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($c['author']); ?></strong>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo htmlspecialchars($c['content']); ?></p>
                                </div>
                            <?php endforeach; ?>

                            <?php if ($auth->isLoggedIn()): ?>
                                <form method="POST" action="" class="mt-3">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                                    <div class="mb-3">
                                        <textarea class="form-control" name="content" rows="2" 
                                                  placeholder="Ajouter un commentaire" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Commenter
                                    </button>
                                </form>
                            <?php else: ?>
                                <p class="text-muted">
                                    <a href="login.php">Connectez-vous</a> pour ajouter un commentaire.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>
                            Par <?php echo htmlspecialchars($p['author'] ?? 'Anonyme'); ?> 
                            le <?php echo date('d/m/Y', strtotime($p['created_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 