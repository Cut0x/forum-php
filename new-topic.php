<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Nouveau sujet</div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input class="form-control" type="text" placeholder="Titre du sujet">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" rows="6" placeholder="Votre message..."></textarea>
                    </div>
                    <button type="button" class="btn btn-primary">Publier</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
