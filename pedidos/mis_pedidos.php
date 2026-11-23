<?php 
$pedidos = $pedidos ?? [];
if (!function_exists('badgeEstado')) {
    function badgeEstado(string $estado): string {
        $clases = [
            'pendiente' => 'text-bg-warning',
            'preparando' => 'text-bg-info',
            'listo' => 'text-bg-primary',
            'entregado' => 'text-bg-success',
            'cancelado' => 'text-bg-secondary',
        ];
        return '<span class="badge ' . ($clases[$estado] ?? 'text-bg-light') . '">' . ucfirst($estado) . '</span>';
    }
}
require __DIR__ . '/../layout/header.php'; 
?>
<h1 class="h3 mb-3">Mis pedidos</h1>
<?php if (!$pedidos): ?>
    <p class="text-muted">Aún no has hecho pedidos. <a href="index.php">Ver el menú</a></p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr><th>#</th><th>Fecha</th><th>Recoger</th><th>Productos</th><th>Total</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td><?= (int) $p['id'] ?></td>
                        <td><?= e($p['fecha']) ?></td>
                        <td><?= e($p['hora']) ?></td>
                        <td><?= e($p['productos']) ?></td>
                        <td>$<?= number_format((float) $p['total'], 2) ?></td>
                        <td><?= badgeEstado($p['estado']) ?></td>
                        <td>
                            <?php if ($p['estado'] === 'pendiente'): ?>
                                <form method="post" action="index.php?accion=cancelar-pedido" class="confirmar"
                                      data-mensaje="¿Cancelar el pedido #<?= (int) $p['id'] ?>?">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Cancelar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>