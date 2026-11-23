<?php 
$esAdmin = $esAdmin ?? (($_SESSION['usuario']['rol'] ?? '') === 'admin');
$productos = $productos ?? [];
$categorias = $categorias ?? ['Comida', 'Bebida', 'Snack', 'Postre'];
$horarios = $horarios ?? ['09:00', '10:30', '12:00', '13:30'];
require __DIR__ . '/../layout/header.php'; 
?>
<h1 class="h3 mb-3">Menú de hoy</h1>
<!-- Filtros por categoría (los maneja app.js) -->
<nav class="mb-4 d-flex flex-wrap gap-2" aria-label="Filtrar por categoría">
    <button type="button" class="btn btn-sm btn-dark" data-filtro="todas">Todas</button>
    <?php foreach ($categorias as $c): ?>
        <button type="button" class="btn btn-sm btn-outline-dark" data-filtro="<?= e($c) ?>"><?= e($c) ?></button>
    <?php endforeach; ?>
</nav>
<div class="row g-4">
    <section class="<?= $esAdmin ? 'col-12' : 'col-lg-8' ?>">
        <?php if (!$productos): ?>
            <p class="text-muted">Todavía no hay productos en el menú.</p>
        <?php endif; ?>
        <div class="row row-cols-1 row-cols-sm-2 <?= $esAdmin ? 'row-cols-lg-4' : 'row-cols-lg-3' ?> g-3">
            <?php foreach ($productos as $p): ?>
                <div class="col">
                    <article class="card h-100 tarjeta producto" data-categoria="<?= e($p['categoria']) ?>">
                        <?php if (!empty($p['imagen'])): ?>
                            <img src="uploads/<?= e($p['imagen']) ?>" class="card-img-top foto" alt="<?= e($p['nombre']) ?>">
                        <?php else: ?>
                            <div class="foto sin-foto" aria-hidden="true">🍽️</div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <span class="badge text-bg-light align-self-start mb-2"><?= e($p['categoria']) ?></span>
                            <h2 class="h6 card-title"><?= e($p['nombre']) ?></h2>
                            <p class="precio mb-2">$<?= number_format((float) $p['precio'], 2) ?></p>
                            <?php if ($esAdmin): ?>
                                <p class="small mb-2">
                                    Existencia: <strong><?= (int) $p['existencia'] ?></strong>
                                    <?php if (empty($p['activo'])): ?><span class="badge text-bg-secondary">Oculto</span><?php endif; ?>
                                </p>
                                <div class="mt-auto d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="index.php?accion=editar&id=<?= (int) $p['id'] ?>">Editar</a>
                                    <form method="post" action="index.php?accion=eliminar" class="confirmar"
                                          data-mensaje="¿Eliminar <?= e($p['nombre']) ?>?">
                                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            <?php elseif ((int) $p['existencia'] === 0): ?>
                                <button type="button" class="btn btn-secondary btn-sm mt-auto" disabled>Agotado</button>
                            <?php else: ?>
                                <p class="small text-muted mb-2">Quedan <?= (int) $p['existencia'] ?></p>
                                <button type="button" class="btn btn-primary btn-sm mt-auto btn-agregar"
                                        data-id="<?= (int) $p['id'] ?>"
                                        data-nombre="<?= e($p['nombre']) ?>"
                                        data-precio="<?= e((string) $p['precio']) ?>"
                                        data-existencia="<?= (int) $p['existencia'] ?>">
                                    Agregar
                                </button>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php if (!$esAdmin): ?>
        <!-- Carrito: lo llena app.js con lo guardado en localStorage -->
        <aside class="col-lg-4">
            <div class="card carrito">
                <div class="card-body">
                    <h2 class="h5">Tu pedido</h2>
                    <ul class="list-unstyled mb-3" id="carrito"></ul>
                    <p class="fw-bold">Total: $<span id="total-carrito">0.00</span></p>
                    <label class="form-label" for="hora-recoger">¿A qué hora lo recoges?</label>
                    <select class="form-select mb-3" id="hora-recoger">
                        <?php foreach ($horarios as $h): ?>
                            <option value="<?= e($h) ?>"><?= e($h) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-success w-100" id="btn-pedir" disabled>Hacer pedido</button>
                    <p class="small mt-2 mb-0" id="mensaje-carrito" aria-live="polite"></p>
                </div>
            </div>
        </aside>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>