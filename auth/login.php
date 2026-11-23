<?php 
$error = $error ?? null;
$correoRecordado = $correoRecordado ?? ($_COOKIE['correo_recordado'] ?? '');
require __DIR__ . '/../layout/header.php'; 
?>
<section class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="h3 mb-3">Iniciar sesión</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?accion=login" class="card card-body">
            <label class="form-label" for="correo">Correo</label>
            <input class="form-control mb-3" type="email" id="correo" name="correo" value="<?= e($correoRecordado) ?>" required>
            <label class="form-label" for="contrasena">Contraseña</label>
            <input class="form-control mb-3" type="password" id="contrasena" name="contrasena" required>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="recordar" name="recordar" <?= $correoRecordado ? 'checked' : '' ?>>
                <label class="form-check-label" for="recordar">Recordar mi correo</label>
            </div>
            <button class="btn btn-primary">Entrar</button>
        </form>
        <p class="mt-3">¿No tienes cuenta? <a href="index.php?accion=registro">Regístrate</a></p>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>