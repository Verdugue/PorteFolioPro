<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Project.php';

$auth = new Auth($pdo);
$project = new Project($pdo);

// Vérifier si l'utilisateur est admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

// Récupérer tous les projets
$projects = $project->getAllProjects();

$pageTitle = 'Gestion des projets';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Gestion des projets</h2>

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

                    <!-- Formulaire d'ajout -->
                    <form method="POST" action="" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Titre</label>
                                    <input type="text" name="title" id="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" name="image" id="image" class="form-control" 
                                           accept=".jpg,.jpeg,.png,.gif">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="external_link" class="form-label">Lien externe</label>
                                    <input type="url" name="external_link" id="external_link" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter le projet</button>
                    </form>

                    <!-- Liste des projets -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Description</th>
                                    <th>Lien</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $p): ?>
                                    <tr>
                                        <td>
                                            <?php if ($p['image_path']): ?>
                                                <img src="<?php echo htmlspecialchars($p['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($p['title']); ?>" 
                                                     style="max-width: 100px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($p['title']); ?></td>
                                        <td><?php echo htmlspecialchars($p['description']); ?></td>
                                        <td>
                                            <?php if (isset($p['external_link']) && trim($p['external_link']) !== ''): ?>
                                                <a href="<?php echo htmlspecialchars($p['external_link']); ?>" 
                                                   target="_blank" class="btn btn-sm btn-primary">
                                                   <i class="fas fa-external-link-alt me-2"></i>Voir le projet
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $p['id']; ?>">
                                                Modifier
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal<?php echo $p['id']; ?>">
                                                Supprimer
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal de modification -->
                                    <div class="modal fade" id="editModal<?php echo $p['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="" enctype="multipart/form-data">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                    
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
                                                                      required><?php echo htmlspecialchars($p['description']); ?></textarea>
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
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                                        <button type="button" class="btn-close" 
                                                                data-bs-dismiss="modal"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <p>Êtes-vous sûr de vouloir supprimer le projet 
                                                           "<?php echo htmlspecialchars($p['title']); ?>" ?</p>
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
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 