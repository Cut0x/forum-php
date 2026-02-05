<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Inscription</div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Pseudo</label>
                        <input class="form-control" type="text" placeholder="Votre pseudo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" placeholder="email@domaine.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input class="form-control" type="password" placeholder="********">
                    </div>
                    <button type="button" class="btn btn-primary w-100">Creer un compte</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
