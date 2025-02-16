<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Project.php';
require_once 'classes/Category.php';
require_once 'classes/Tag.php';

$auth = new Auth($pdo);
$project = new Project($pdo);
$category = new Category($pdo);
$tag = new Tag($pdo);

// Vérifier si l'utilisateur est connecté
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $result = $project->addProject(
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'external_link' => $_POST['external_link']
                    ],
                    $_FILES['image'] ?? null
                );
                if ($result['success']) {
                    // Si des tags sont sélectionnés, les ajouter au projet
                    if (!empty($_POST['tags'])) {
                        $tag->addProjectTags($result['id'], $_POST['tags']);
                    }
                    $message = 'Projet ajouté avec succès.';
                } else {
                    $error = $result['message'];
                }
                break;

            case 'edit':
                if (isset($_POST['id'])) {
                    $result = $project->updateProject(
                        (int)$_POST['id'],
                        [
                            'title' => $_POST['title'],
                            'description' => $_POST['description'],
                            'external_link' => $_POST['external_link']
                        ],
                        $_FILES['image'] ?? null
                    );
                    if ($result['success']) {
                        // Mettre à jour les tags
                        if (isset($_POST['tags'])) {
                            $tag->addProjectTags((int)$_POST['id'], $_POST['tags']);
                        }
                        $message = 'Projet mis à jour avec succès.';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    $result = $project->deleteProject((int)$_POST['id']);
                    if ($result['success']) {
                        $message = 'Projet supprimé avec succès.';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
        }
    }
}

// Récupérer les projets de l'utilisateur
$userProjects = $project->getUserProjects($_SESSION['user_id']);
// Récupérer toutes les catégories et tags pour les formulaires
$categories = $category->getAllCategories();
$allTags = $tag->getAllTags();

$pageTitle = 'Mes Projets';
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Gérer mes projets</h2>

                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire d'ajout -->
                    <form method="POST" action="" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-heading me-2"></i>Titre
                                    </label>
                                    <input type="text" name="title" id="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="external_link" class="form-label">
                                        <i class="fas fa-link me-2"></i>Lien externe
                                    </label>
                                    <input type="url" name="external_link" id="external_link" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Description
                            </label>
                            <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-image me-2"></i>Image
                                    </label>
                                    <input type="file" name="image" id="image" class="form-control" 
                                           accept=".jpg,.jpeg,.png,.gif">
                                    <div class="form-text">
                                        Formats acceptés : JPG, JPEG, PNG, GIF. Taille maximale : 5MB
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tags me-2"></i>Tags
                                    </label>
                                    <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                        <?php foreach ($allTags as $t): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="tags[]" value="<?php echo $t['id']; ?>"
                                                       id="tag<?php echo $t['id']; ?>">
                                                <label class="form-check-label" for="tag<?php echo $t['id']; ?>">
                                                    <?php echo htmlspecialchars($t['name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Ajouter le projet
                            </button>
                        </div>
                    </form>

                    <!-- Liste des projets -->
                    <h3 class="mb-4">Mes projets</h3>
                    <?php if (empty($userProjects)): ?>
                        <p class="text-muted">Vous n'avez pas encore de projets.</p>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($userProjects as $p): ?>
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
                                                   class="btn btn-primary btn-sm" 
                                                   target="_blank">
                                                    <i class="fas fa-external-link-alt me-2"></i>Voir le projet
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer d-flex justify-content-between">
                                            <button type="button" class="btn btn-primary btn-sm"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $p['id']; ?>">
                                                <i class="fas fa-edit me-2"></i>Modifier
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm ms-2"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal<?php echo $p['id']; ?>">
                                                <i class="fas fa-trash-alt me-2"></i>Supprimer
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Modal de modification -->
                                    <div class="modal fade" id="editModal<?php echo $p['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form method="POST" action="" enctype="multipart/form-data">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                    <input type="hidden" name="csrf_token" 
                                                           value="<?php echo $auth->generateCsrfToken(); ?>">
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier le projet</h5>
                                                        <button type="button" class="btn-close" 
                                                                data-bs-dismiss="modal"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Titre</label>
                                                            <input type="text" name="title" class="form-control" 
                                                                   value="<?php echo htmlspecialchars($p['title']); ?>" 
                                                                   required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="form-control" 
                                                                      rows="4" required><?php echo htmlspecialchars($p['description']); ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Image</label>
                                                            <?php if ($p['image_path']): ?>
                                                                <div class="mb-2">
                                                                    <img src="<?php echo htmlspecialchars($p['image_path']); ?>" 
                                                                         alt="Image actuelle" 
                                                                         style="max-width: 200px;">
                                                                </div>
                                                            <?php endif; ?>
                                                            <input type="file" name="image" class="form-control" 
                                                                   accept=".jpg,.jpeg,.png,.gif">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Lien externe</label>
                                                            <input type="url" name="external_link" class="form-control" 
                                                                   value="<?php echo htmlspecialchars($p['external_link'] ?? ''); ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Tags</label>
                                                            <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                                                <?php 
                                                                $projectTags = $tag->getProjectTags($p['id']);
                                                                $projectTagIds = array_column($projectTags, 'id');
                                                                foreach ($allTags as $t): 
                                                                ?>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" 
                                                                               name="tags[]" value="<?php echo $t['id']; ?>"
                                                                               id="editTag<?php echo $p['id']; ?>_<?php echo $t['id']; ?>"
                                                                               <?php echo in_array($t['id'], $projectTagIds) ? 'checked' : ''; ?>>
                                                                        <label class="form-check-label" 
                                                                               for="editTag<?php echo $p['id']; ?>_<?php echo $t['id']; ?>">
                                                                            <?php echo htmlspecialchars($t['name']); ?>
                                                                        </label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" 
                                                                data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            Enregistrer
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal de suppression -->
                                    <div class="modal fade" id="deleteModal<?php echo $p['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="" id="deleteForm<?php echo $p['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                    <input type="hidden" name="csrf_token" 
                                                           value="<?php echo $auth->generateCsrfToken(); ?>">
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                                        <button type="button" class="btn-close" 
                                                                data-bs-dismiss="modal"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <p>Êtes-vous sûr de vouloir supprimer le projet 
                                                           "<?php echo htmlspecialchars($p['title']); ?>" ?</p>
                                                        <p class="text-danger">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            Cette action est irréversible.
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" 
                                                                data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            Supprimer
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
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

<!-- Script pour gérer les modales -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour nettoyer l'overlay et autres éléments de modal
    function cleanupModal() {
        document.body.classList.remove('modal-open');
        const modalBackdrops = document.querySelectorAll('.modal-backdrop');
        modalBackdrops.forEach(backdrop => {
            backdrop.classList.remove('show');
            setTimeout(() => backdrop.remove(), 200);
        });
    }

    // Gérer les modales de suppression
    const deleteModals = document.querySelectorAll('[id^="deleteModal"]');
    deleteModals.forEach(modal => {
        const form = modal.querySelector('form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
            cleanupModal();
            setTimeout(() => this.submit(), 300);
        });
    });

    // Gérer les modales de modification
    const editModals = document.querySelectorAll('[id^="editModal"]');
    editModals.forEach(modal => {
        const form = modal.querySelector('form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
            cleanupModal();
            setTimeout(() => this.submit(), 300);
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 