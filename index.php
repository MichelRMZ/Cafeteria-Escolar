<?php
    session_start();

    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/models/Usuario.php';
    require_once __DIR__ . '/models/Producto.php';
    require_once __DIR__ . '/models/Pedido.php';
    require_once __DIR__ . '/controllers/Controller.php';
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/ProductoController.php';
    require_once __DIR__ . '/controllers/PedidoController.php';

    // Escapa texto antes de mostrarlo en HTML (evita XSS)
    function e(?string $texto): string {
        return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
    }

    // Etiqueta de color segun el estado del pedido (el color siempre va con texto)
    function badgeEstado(string $estado): string {
        $clases = [
            'pendiente' => 'text-bg-warning',
            'preparando' => 'text-bg-info',
            'listo' => 'text-bg-primary',
            'entregado' => 'text-bg-success',
            'cancelado' => 'text-bg-secondary',
        ];
        return '<span class="badge ' . ($clases[$estado] ?? 'text-bg-light') . '">' . e(ucfirst($estado)) . '</span>';
    }

    $accion = $_GET['accion'] ?? 'menu';

    try {
        $db = conectar();
        $auth = new AuthController($db);
        $productos = new ProductoController($db);
        $pedidos = new PedidoController($db);

        match ($accion) {
            'login' => $auth->login(),
            'registro' => $auth->registro(),
            'salir' => $auth->salir(),
            'nuevo' => $productos->formulario(),
            'editar' => $productos->formulario((int) ($_GET['id'] ?? 0)),
            'guardar' => $productos->guardar(),
            'eliminar' => $productos->eliminar(),
            'mis-pedidos' => $pedidos->misPedidos(),
            'cancelar-pedido' => $pedidos->cancelar(),
            'panel' => $pedidos->panel(),
            'estado-pedido' => $pedidos->cambiarEstado(),
            default => $pedidos->menu(),
        };
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $error = 'No pudimos conectar con la base de datos. Intenta mas tarde.';
        require __DIR__ . '/views/error.php';
    }
?>