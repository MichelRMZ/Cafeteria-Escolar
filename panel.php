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
// Estado actual => [estado siguiente, texto del botón]
$siguiente = [
    'pendiente' => ['preparando', 'Preparar'],
    'preparando' => ['listo', 'Listo'],
    'listo' => ['entregado', 'Entregado'],
];
require __DIR__ . '/../layout/header.php'; 
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Pedidos de hoy</h1>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?accion=panel">Actualizar</a>
</div>
<?php if (!$pedidos): ?>
    <p class="text-muted">No hay pedidos para hoy.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr><th>Recoger</th><th>#</th><th>Alumno</th><th>Productos</th><th>Total</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr class="<?= in_array($p['estado'], ['entregado', 'cancelado'], true) ? 'text-muted' : '' ?>">
                        <td class="fw-bold"><?= e($p['hora']) ?></td>
                        <td><?= (int) $p['id'] ?></td>
                        <td><?= e($p['alumno']) ?></td>
                        <td><?= e($p['productos']) ?></td>
                        <td>$<?= number_format((float) $p['total'], 2) ?></td>
                        <td><?= badgeEstado($p['estado']) ?></td>
                        <td class="d-flex gap-2">
                            <?php if (isset($siguiente[$p['estado']])): ?>
                                <?php [$nuevo, $texto] = $siguiente[$p['estado']]; ?>
                                <form method="post" action="index.php?accion=estado-pedido">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <input type="hidden" name="estado" value="<?= $nuevo ?>">
                                    <button class="btn btn-sm btn-primary"><?= $texto ?></button>
                                </form>
                                <form method="post" action="index.php?accion=estado-pedido" class="confirmar"
                                      data-mensaje="¿Cancelar el pedido #<?= (int) $p['id'] ?>? Los productos regresan a la existencia.">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <input type="hidden" name="estado" value="cancelado">
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