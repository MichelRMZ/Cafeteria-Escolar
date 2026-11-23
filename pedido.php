<?php
    session_start();
    require __DIR__ . '/../config/db.php';
    require __DIR__ . '/../models/Pedido.php';

    header('Content-Type: application/json; charset=uft-8');

    //Responde en JSON con un codigo HTTP y termina
    function responder(int $codigo, array $datos): void {
        http_response_code($codigo);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $usuario = $_SESSION['usuario'] ?? null;
    if (!$usuario) {
        responder(401, ['ok' => false, 'mensaje' => 'Tu sesion termino. Vuelve a iniciar sesion.']);
    }
    if ($usuario['rol'] !== 'alumno') {
        responder(403, ['ok' => false, 'mensaje' => 'Solo los alumnos pueden hacer pedidos.']);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder(405, ['ok' => false, 'mensaje' => 'Metodo no permitido.']);
    }

    //Lee el cuerpo de la peticion: {"items":[{"id":1,"cantidad":2}], "hora":"10:30"}
    $entrada = json_decode(file_get_contents('php://input'), true);
    $hora = $entrada['hora'] ?? '';
    $lista = $entrada['items'] ?? null;

    if (!in_array($hora, Pedido::HORARIOS, true)) {
        responder(422, ['ok' => false, 'mensaje' => 'Elige una hora de recogida de la lista.']);
    }
    if (!is_array($lista) || !$lista) {
        responder(422, ['ok' => false, 'mensaje' => 'Tu carrito esta vacio']);
    }

    // Convierte la lista en [productoId => cantidad] y junta productos repetidos
    $items = [];
    foreach ($lista as $item) {
        $id = filter_var($item[$id] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $cantidad = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
        if ($id === false || $cantidad === false) {
            responder(422, ['ok' => false, 'mensaje' => 'Cada producto debe tener una cantidad de 1 a 10.']);
        }
        $items[$id] = ($items[$id] ?? 0) + $cantidad;
    }

    if (count($items) > 10 || max($items) > 10) {
        responder(422, ['ok' => false, 'mensaje' => 'Maximo 10 productos distintos y 10 piezas de cada uno.']);
    }

    try {
        $pedidos = new Pedido(conectar());
        $pedidoId = $pedidos->crear($usuario['id'], $items, $hora);

        $mensaje = "Pedido #$pedidoId recibido. Recogelo a las $hora.";
        $_SESSION['aviso'] = $mensaje;
        responder(201, ['ok' => true, 'mensaje' => $mensaje, 'pedido_id' => $pedidoId]);
    } catch (InvalidArgumentException $ex) {
        // Por Ejemplo: "Solo quedan 2 de Torta de Jamon."
        responder(409, ['ok' => false, 'mensaje' => $ex->getMessage()]);
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        responder(500, ['ok' => false, 'mensaje' => 'No pudimos guardar tu pedido. Intenta de nuevo.']);
    }
?>