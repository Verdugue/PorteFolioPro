<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Tag.php';

$auth = new Auth($pdo);
$tag = new Tag($pdo);

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
                $result = $tag->addTag($_POST['name']);
                if ($result['success']) {
                    $message = 'Tag ajouté avec succès.';
                } else {
                    $error = $result['message'];
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    $result = $tag->deleteTag((int)$_POST['id']);
                    if ($result['success']) {
                        $message = 'Tag supprimé avec succès.';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
        }
    }
}

// Récupérer tous les tags
$tags = $tag->getAllTags();

$pageTitle = 'Gestion des tags';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Gestion des tags</h2>

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
                    <form method="POST" action="" class="mb-4">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" name="name" class="form-control" 
                                       placeholder="Nom du tag" required
                                       pattern="[A-Za-z0-9\-\.]+"
                                       title="Lettres, chiffres, tirets et points uniquement">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Utilisez uniquement des lettres, chiffres, tirets et points
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Liste des tags -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Nombre de projets</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tags as $t): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($t['name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $projectCount = count($tag->searchProjectsByTags([$t['id']]));
                                            echo $projectCount;
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal<?php echo $t['id']; ?>">
                                                <i class="fas fa-trash-alt me-2"></i>Supprimer
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal de suppression -->
                                    <div class="modal fade" id="deleteModal<?php echo $t['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                                        <button type="button" class="btn-close" 
                                                                data-bs-dismiss="modal"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <p>Êtes-vous sûr de vouloir supprimer le tag 
                                                           "<?php echo htmlspecialchars($t['name']); ?>" ?</p>
                                                        <?php if ($projectCount > 0): ?>
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                Ce tag est utilisé par <?php echo $projectCount; ?> projet(s).
                                                                La suppression retirera le tag de tous ces projets.
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

                    <?php if (empty($tags)): ?>
                        <p class="text-muted text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun tag n'a encore été créé.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 