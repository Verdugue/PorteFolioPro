<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Skill.php';

$auth = new Auth($pdo);
$skill = new Skill($pdo);

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
                $result = $skill->addSkill(
                    $_POST['name'],
                    $_POST['description'] ?? ''
                );
                if ($result['success']) {
                    $message = 'Compétence ajoutée avec succès.';
                } else {
                    $error = $result['message'];
                }
                break;

            case 'edit':
                if (isset($_POST['id'])) {
                    $result = $skill->updateSkill(
                        (int)$_POST['id'],
                        $_POST['name'],
                        $_POST['description'] ?? ''
                    );
                    if ($result['success']) {
                        $message = 'Compétence mise à jour avec succès.';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    $result = $skill->deleteSkill((int)$_POST['id']);
                    if ($result['success']) {
                        $message = 'Compétence supprimée avec succès.';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
        }
    }
}

// Récupérer toutes les compétences
$skills = $skill->getAllSkills();

$pageTitle = 'Gestion des compétences';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Gestion des compétences</h2>

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
                                       placeholder="Nom de la compétence" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="description" class="form-control" 
                                       placeholder="Description (optionnelle)">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    Ajouter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Liste des compétences -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($skills as $s): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                                        <td><?php echo htmlspecialchars($s['description']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $s['id']; ?>">
                                                Modifier
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal<?php echo $s['id']; ?>">
                                                <i class="fas fa-trash-alt me-2"></i>Supprimer
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal de modification -->
                                    <div class="modal fade" id="editModal<?php echo $s['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier la compétence</h5>
                                                        <button type="button" class="btn-close" 
                                                                data-bs-dismiss="modal"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nom</label>
                                                            <input type="text" name="name" class="form-control" 
                                                                   value="<?php echo htmlspecialchars($s['name']); ?>" 
                                                                   required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <input type="text" name="description" 
                                                                   class="form-control" 
                                                                   value="<?php echo htmlspecialchars($s['description']); ?>">
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
                                    <div class="modal fade" id="deleteModal<?php echo $s['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content bg-danger text-white">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                                    
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            Confirmer la suppression
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" 
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <p class="mb-0">Êtes-vous sûr de vouloir supprimer la compétence 
                                                           "<strong><?php echo htmlspecialchars($s['name']); ?></strong>" ?</p>
                                                        <p class="mt-2 mb-0 text-white-50">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Cette action est irréversible et supprimera également cette compétence 
                                                            du profil de tous les utilisateurs qui l'ont sélectionnée.
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-outline-light" 
                                                                data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-2"></i>Annuler
                                                        </button>
                                                        <button type="submit" class="btn btn-light text-danger">
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