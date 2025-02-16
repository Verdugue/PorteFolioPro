<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Comment.php';

$auth = new Auth($pdo);
$comment = new Comment($pdo);

// Vérifier si l'utilisateur est admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

// Traitement des actions de modération
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Session expirée. Veuillez réessayer.';
    } else if (isset($_POST['action'], $_POST['comment_id'])) {
        $commentId = (int)$_POST['comment_id'];
        
        switch ($_POST['action']) {
            case 'approve':
                $result = $comment->moderateComment($commentId, 'approved');
                if ($result['success']) {
                    $message = 'Commentaire approuvé avec succès.';
                } else {
                    $error = $result['message'];
                }
                break;

            case 'reject':
                $result = $comment->moderateComment($commentId, 'rejected');
                if ($result['success']) {
                    $message = 'Commentaire rejeté avec succès.';
                } else {
                    $error = $result['message'];
                }
                break;

            case 'delete':
                $result = $comment->deleteComment($commentId);
                if ($result['success']) {
                    $message = 'Commentaire supprimé avec succès.';
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Récupérer les commentaires en attente
$pendingComments = $comment->getPendingComments();

$pageTitle = 'Modération des commentaires';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Modération des commentaires</h2>

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

                    <?php if (empty($pendingComments)): ?>
                        <p class="text-muted">Aucun commentaire en attente de modération.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Auteur</th>
                                        <th>Projet</th>
                                        <th>Commentaire</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingComments as $c): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($c['author']); ?></td>
                                            <td><?php echo htmlspecialchars($c['project_title']); ?></td>
                                            <td><?php echo htmlspecialchars($c['content']); ?></td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="csrf_token" 
                                                           value="<?php echo $auth->generateCsrfToken(); ?>">
                                                    <input type="hidden" name="comment_id" 
                                                           value="<?php echo $c['id']; ?>">
                                                    
                                                    <button type="submit" name="action" value="approve" 
                                                            class="btn btn-success btn-sm">
                                                        Approuver
                                                    </button>
                                                    <button type="submit" name="action" value="reject" 
                                                            class="btn btn-warning btn-sm">
                                                        Rejeter
                                                    </button>
                                                    <button type="submit" name="action" value="delete" 
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">
                                                        Supprimer
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 