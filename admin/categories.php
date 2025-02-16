<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Category.php';

$auth = new Auth($pdo);
$category = new Category($pdo);

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
                $result = $category->addCategory(
                    $_POST['name'],
                    $_POST['description'] ?? ''
                );
                if ($result['success']) {
                    $message = 'Catégorie ajoutée avec succès.';
                } else {
                    $error = $result['message'];
                }
                break;

            case 'edit':
                if (isset($_POST['id'])) {
                    $result = $category->updateCategory(
                        (int)$_POST['id'],
                        $_POST['name'],
                        $_POST['description'] ?? ''
                    );
                    if ($result['success']) {
                        $message = 'Catégorie mise à jour avec succès.';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    $result = $category->deleteCategory((int)$_POST['id']);
                    if ($result['success']) {
                        $message = 'Catégorie supprimée avec succès.';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
        }
    }
}

// Récupérer toutes les catégories
$categories = $category->getAllCategories();

$pageTitle = 'Gestion des catégories';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Gestion des catégories</h2>

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
                    <form method="POST" action="" class="mb-4">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="name" class="form-control" 
                                       placeholder="Nom de la catégorie" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="description" class="form-control" 
                                       placeholder="Description (optionnelle)">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Liste des catégories -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Nombre de projets</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                        <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                        <td>
                                            <?php echo $category->countCategoryProjects($cat['id']); ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $cat['id']; ?>">
                                                <i class="fas fa-edit me-2"></i>Modifier
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal<?php echo $cat['id']; ?>">
                                                <i class="fas fa-trash-alt me-2"></i>Supprimer
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal de modification -->
                                    <div class="modal fade" id="editModal<?php echo $cat['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier la catégorie</h5>
                                                        <button type="button" class="btn-close" 
                                                                data-bs-dismiss="modal"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nom</label>
                                                            <input type="text" name="name" class="form-control" 
                                                                   value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                                                   required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <input type="text" name="description" 
                                                                   class="form-control" 
                                                                   value="<?php echo htmlspecialchars($cat['description']); ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" 
                                                                data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save me-2"></i>Enregistrer
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal de suppression -->
                                    <div class="modal fade" id="deleteModal<?php echo $cat['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                                        <button type="button" class="btn-close" 
                                                                data-bs-dismiss="modal"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <p>Êtes-vous sûr de vouloir supprimer la catégorie 
                                                           "<?php echo htmlspecialchars($cat['name']); ?>" ?</p>
                                                        <?php if ($category->countCategoryProjects($cat['id']) > 0): ?>
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                Cette catégorie contient des projets. La suppression 
                                                                affectera ces projets.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" 
                                                                data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash-alt me-2"></i>Supprimer
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