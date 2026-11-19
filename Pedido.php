<?php
class Pedido
{
    // Horas en las que se pueden recoger los pedidos (recesos)
    public const HORARIOS = ['09:00', '10:30', '12:00', '13:30'];

    public function __construct(private PDO $db) {}

    // $items = [id_producto => cantidad]. Devuelve el número del pedido.
    public function crear(int $usuarioId, array $items, string $hora): int
    {
        ksort($items);   // siempre en el mismo orden, para que dos pedidos no se bloqueen entre sí
        $this->db->beginTransaction();
        try {
            $buscar = $this->db->prepare(
                'SELECT nombre, precio, existencia, activo FROM productos WHERE id = ? FOR UPDATE'
            );
            $total = 0;
            $renglones = [];

            // 1. Revisa cada producto y calcula el total con los precios de la base, no los del navegador
            foreach ($items as $productoId => $cantidad) {
                $buscar->execute([$productoId]);
                $producto = $buscar->fetch();
                if (!$producto || !$producto['activo']) {
                    throw new InvalidArgumentException('Un producto de tu carrito ya no está disponible.');
                }
                if ($cantidad > $producto['existencia']) {
                    throw new InvalidArgumentException(
                        "Solo quedan {$producto['existencia']} de {$producto['nombre']}."
                    );
                }
                $total += $producto['precio'] * $cantidad;
                $renglones[] = [$productoId, $cantidad, $producto['precio']];
            }

            // 2. Guarda el pedido
            $consulta = $this->db->prepare(
                'INSERT INTO pedidos (usuario_id, total, hora_recoger) VALUES (?, ?, ?) RETURNING id'
            );
            $consulta->execute([$usuarioId, $total, $hora]);
            $pedidoId = (int) $consulta->fetchColumn();

            // 3. Guarda cada producto del pedido y descuenta la existencia
            $detalle = $this->db->prepare(
                'INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)'
            );
            $descontar = $this->db->prepare('UPDATE productos SET existencia = existencia - ? WHERE id = ?');
            foreach ($renglones as [$productoId, $cantidad, $precio]) {
                $detalle->execute([$pedidoId, $productoId, $cantidad, $precio]);
                $descontar->execute([$cantidad, $productoId]);
            }

            $this->db->commit();      // todo salió bien
            return $pedidoId;
        } catch (Throwable $ex) {
            $this->db->rollBack();    // algo falló: no se guarda nada
            throw $ex;
        }
    }

    public function buscarPorId(int $id): ?array
    {
        $consulta = $this->db->prepare('SELECT * FROM pedidos WHERE id = ?');
        $consulta->execute([$id]);
        return $consulta->fetch() ?: null;
    }

    // Pedidos de un alumno con la lista de productos en un solo texto
    public function delUsuario(int $usuarioId): array
    {
        $sql = "SELECT p.id, p.total, p.estado,
                       to_char(p.hora_recoger, 'HH24:MI') AS hora,
                       to_char(p.creado_en, 'DD/MM/YYYY') AS fecha,
                       string_agg(d.cantidad || ' × ' || pr.nombre, ', ') AS productos
                FROM pedidos p
                JOIN pedido_detalle d ON d.pedido_id = p.id
                JOIN productos pr ON pr.id = d.producto_id
                WHERE p.usuario_id = ?
                GROUP BY p.id
                ORDER BY p.creado_en DESC
                LIMIT 20";
        $consulta = $this->db->prepare($sql);
        $consulta->execute([$usuarioId]);
        return $consulta->fetchAll();
    }

    // Pedidos de hoy para el panel del administrador, ordenados por hora de recoger
    public function deHoy(): array
    {
        $sql = "SELECT p.id, p.total, p.estado, u.nombre AS alumno,
                       to_char(p.hora_recoger, 'HH24:MI') AS hora,
                       string_agg(d.cantidad || ' × ' || pr.nombre, ', ') AS productos
                FROM pedidos p
                JOIN usuarios u ON u.id = p.usuario_id
                JOIN pedido_detalle d ON d.pedido_id = p.id
                JOIN productos pr ON pr.id = d.producto_id
                WHERE p.creado_en::date = CURRENT_DATE
                GROUP BY p.id, u.nombre
                ORDER BY p.hora_recoger, p.id";
        return $this->db->query($sql)->fetchAll();
    }

    // Cambia el estado; si se cancela, devuelve las piezas a la existencia
    public function cambiarEstado(int $pedidoId, string $estado): void
    {
        $this->db->beginTransaction();
        try {
            $consulta = $this->db->prepare('SELECT estado FROM pedidos WHERE id = ? FOR UPDATE');
            $consulta->execute([$pedidoId]);
            $actual = $consulta->fetchColumn();
            if ($actual === false || in_array($actual, ['entregado', 'cancelado'], true)) {
                throw new InvalidArgumentException('Ese pedido ya no se puede cambiar.');
            }

            if ($estado === 'cancelado') {
                $sql = 'UPDATE productos pr SET existencia = pr.existencia + d.cantidad
                        FROM pedido_detalle d
                        WHERE d.pedido_id = ? AND d.producto_id = pr.id';
                $this->db->prepare($sql)->execute([$pedidoId]);
            }

            $this->db->prepare('UPDATE pedidos SET estado = ? WHERE id = ?')->execute([$estado, $pedidoId]);
            $this->db->commit();
        } catch (Throwable $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }
}
