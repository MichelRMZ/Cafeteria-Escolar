<?php 
$p = $producto ?? [];
$error = $error ?? null;
$categorias = $categorias ?? ['Comida', 'Bebida', 'Snack', 'Postre'];
require __DIR__ . '/../layout/header.php'; 
?>
<section class="row justify-content-center">
    <div class="col-md-7">
        <h1 class="h3 mb-3"><?= !empty($p['id']) ? 'Editar producto' : 'Nuevo producto' ?></h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?accion=guardar" enctype="multipart/form-data" class="card card-body">
            <input type="hidden" name="id" value="<?= (int) ($p['id'] ?? 0) ?>">
            <label class="form-label" for="nombre">Nombre</label>
            <input class="form-control mb-3" id="nombre" name="nombre" value="<?= e($p['nombre'] ?? '') ?>" minlength="3" maxlength="80" required>
            <label class="form-label" for="categoria">Categoría</label>
            <select class="form-select mb-3" id="categoria" name="categoria" required>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= e($c) ?>" <?= ($p['categoria'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="row">
                <div class="col-6">
                    <label class="form-label" for="precio">Precio ($)</label>
                    <input class="form-control mb-3" type="number" step="0.01" min="0" id="precio" name="precio" value="<?= e((string) ($p['precio'] ?? '')) ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label" for="existencia">Existencia (piezas)</label>
                    <input class="form-control mb-3" type="number" min="0" max="999" id="existencia" name="existencia" value="<?= (int) ($p['existencia'] ?? 0) ?>" required>
                </div>
            </div>
            <label class="form-label" for="imagen">Foto (JPG, PNG o WEBP, máximo 2 MB)</label>
            <input class="form-control mb-2" type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp">
            <img id="vista-previa" class="vista-previa mb-3 <?= empty($p['imagen']) ? 'ocultar' : '' ?>"
                 src="<?= !empty($p['imagen']) ? 'uploads/' . e($p['imagen']) : '' ?>" alt="Vista previa de la foto">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="activo" name="activo" <?= ($p['activo'] ?? true) ? 'checked' : '' ?>>
                <label class="form-check-label" for="activo">Mostrar en el menú</label>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Guardar</button>
                <a class="btn btn-outline-secondary" href="index.php">Cancelar</a>
            </div>
        </form>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>