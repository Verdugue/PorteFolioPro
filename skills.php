<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Skill.php';

$auth = new Auth($pdo);
$skill = new Skill($pdo);

// Vérifier si l'utilisateur est connecté
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Traitement de la mise à jour des compétences
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['skill_id']) && isset($_POST['level'])) {
        // Debug information
        error_log("Attempting to update skill: User ID=" . $_SESSION['user_id'] . 
                 ", Skill ID=" . $_POST['skill_id'] . 
                 ", Level=" . $_POST['level']);
        
        $result = $skill->updateUserSkill(
            $_SESSION['user_id'],
            (int)$_POST['skill_id'],
            $_POST['level']
        );
        if ($result['success']) {
            $message = 'Compétence mise à jour avec succès.';
        } else {
            $error = $result['message'];
            error_log("Error updating skill: " . $error);
        }
    } elseif (isset($_POST['remove_skill'])) {
        $result = $skill->removeUserSkill(
            $_SESSION['user_id'],
            (int)$_POST['remove_skill']
        );
        if ($result['success']) {
            $message = 'Compétence supprimée avec succès.';
        } else {
            $error = $result['message'];
            error_log("Error removing skill: " . $error);
        }
    }
}

// Récupérer toutes les compétences disponibles
$allSkills = $skill->getAllSkills();
// Récupérer les compétences de l'utilisateur
$userSkills = $skill->getUserSkills($_SESSION['user_id']);

// Créer un tableau des IDs des compétences de l'utilisateur pour faciliter la vérification
$userSkillIds = array_column($userSkills, 'id');

$pageTitle = 'Mes compétences';
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Mes compétences</h2>

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

                    <!-- Ajouter une nouvelle compétence -->
                    <div class="mb-4">
                        <h3>Ajouter une compétence</h3>
                        <form method="POST" action="" class="row g-3">
                            <div class="col-md-6">
                                <select name="skill_id" class="form-select" required>
                                    <option value="">Choisir une compétence</option>
                                    <?php foreach ($allSkills as $s): ?>
                                        <?php if (!in_array($s['id'], $userSkillIds)): ?>
                                            <option value="<?php echo $s['id']; ?>">
                                                <?php echo htmlspecialchars($s['name']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="level" class="form-select" required>
                                    <option value="debutant">Débutant</option>
                                    <option value="intermediaire">Intermédiaire</option>
                                    <option value="avance">Avancé</option>
                                    <option value="expert">Expert</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Ajouter</button>
                            </div>
                        </form>
                    </div>

                    <!-- Liste des compétences de l'utilisateur -->
                    <h3>Mes compétences actuelles</h3>
                    <?php if (empty($userSkills)): ?>
                        <p class="text-muted">Vous n'avez pas encore ajouté de compétences.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Compétence</th>
                                        <th>Description</th>
                                        <th>Niveau</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userSkills as $s): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                                            <td><?php echo htmlspecialchars($s['description']); ?></td>
                                            <td>
                                                <form method="POST" action="" class="d-flex align-items-center">
                                                    <input type="hidden" name="skill_id" 
                                                           value="<?php echo $s['id']; ?>">
                                                    <select name="level" class="form-select form-select-sm me-2" 
                                                            onchange="this.form.submit()">
                                                        <option value="debutant" <?php echo $s['level'] === 'debutant' ? 'selected' : ''; ?>>
                                                            Débutant
                                                        </option>
                                                        <option value="intermediaire" <?php echo $s['level'] === 'intermediaire' ? 'selected' : ''; ?>>
                                                            Intermédiaire
                                                        </option>
                                                        <option value="avance" <?php echo $s['level'] === 'avance' ? 'selected' : ''; ?>>
                                                            Avancé
                                                        </option>
                                                        <option value="expert" <?php echo $s['level'] === 'expert' ? 'selected' : ''; ?>>
                                                            Expert
                                                        </option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="remove_skill" 
                                                           value="<?php echo $s['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette compétence ?')">
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

<?php require_once 'includes/footer.php'; ?> 