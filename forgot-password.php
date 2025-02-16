<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth($pdo);

// Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result = $auth->initiatePasswordReset($email);
        
        if ($result['success']) {
            $message = "Si un compte existe avec cette adresse email, un lien de réinitialisation vous a été envoyé. 
                       Vérifiez votre boîte de réception et vos spams.";
        } else {
            $error = $result['message'];
        }
    } else {
        $error = "Veuillez entrer une adresse email valide";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Mot de passe oublié</h2>

                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$message): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        Réinitialiser le mot de passe
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <p><a href="login.php">Retour à la connexion</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 