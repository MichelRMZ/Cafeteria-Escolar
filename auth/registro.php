<?php 
$error = $error ?? null;
$nombre = $nombre ?? '';
$correo = $correo ?? '';
require __DIR__ . '/../layout/header.php'; 
?>
<section class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="h3 mb-3">Crear cuenta</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?accion=registro" class="card card-body" id="form-registro">
            <label class="form-label" for="nombre">Nombre completo</label>
            <input class="form-control mb-3" id="nombre" name="nombre" value="<?= e($nombre) ?>"
                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,80}" title="Solo letras y espacios, mínimo 3" required>
            <label class="form-label" for="correo">Correo</label>
            <input class="form-control mb-3" type="email" id="correo" name="correo" value="<?= e($correo) ?>" required>
            <label class="form-label" for="contrasena">Contraseña (mínimo 8 caracteres)</label>
            <input class="form-control mb-3" type="password" id="contrasena" name="contrasena" minlength="8" required>
            <label class="form-label" for="confirmar">Repite la contraseña</label>
            <input class="form-control mb-1" type="password" id="confirmar" minlength="8" required>
            <p class="text-danger small ocultar" id="error-confirmar">Las contraseñas no coinciden.</p>
            <button class="btn btn-primary mt-2">Registrarme</button>
        </form>
        <p class="mt-3">¿Ya tienes cuenta? <a href="index.php?accion=login">Inicia sesión</a></p>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>