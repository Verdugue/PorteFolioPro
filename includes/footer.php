    </div><!-- Fin du container -->

    <footer class="footer mt-5 py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>À propos</h5>
                    <p class="text-muted">
                        DevShowcase - Plateforme professionnelle pour présenter vos projets et compétences.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Liens utiles</h5>
                    <ul class="list-unstyled">
                        <?php
                        // Détecter si nous sommes dans l'administration
                        $isAdmin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
                        ?>
                        <li><a href="<?php echo $isAdmin ? '../projects.php' : 'projects.php'; ?>" class="text-muted">Projets</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="<?php echo $isAdmin ? '../my-projects.php' : 'my-projects.php'; ?>" class="text-muted">Mes Projets</a></li>
                            <li><a href="<?php echo $isAdmin ? '../skills.php' : 'skills.php'; ?>" class="text-muted">Compétences</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo $isAdmin ? '../login.php' : 'login.php'; ?>" class="text-muted">Connexion</a></li>
                            <li><a href="<?php echo $isAdmin ? '../register.php' : 'register.php'; ?>" class="text-muted">Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr>
            
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 