<?php 
$error = $error ?? 'Ocurrió un error inesperado.';
require __DIR__ . '/layout/header.php'; 
?>
<section class="text-center py-5">
    <h1 class="h3">Algo salió mal</h1>
    <p class="text-muted"><?= e($error) ?></p>
    <a class="btn btn-primary" href="index.php">Volver al menú</a>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>