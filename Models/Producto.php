<?php
class Producto
{
    public const CATEGORIAS = ['Comida', 'Bebida', 'Snack', 'Postre'];

    public function __construct(private PDO $db) {}

    // El alumno solo ve los productos activos; el administrador ve todos
    public function todos(bool $soloActivos): array
    {
        $sql = 'SELECT * FROM productos'
             . ($soloActivos ? ' WHERE activo = TRUE' : '')
             . ' ORDER BY categoria, nombre';
        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $consulta = $this->db->prepare('SELECT * FROM productos WHERE id = ?');
        $consulta->execute([$id]);
        return $consulta->fetch() ?: null;
    }

    public function crear(array $d): void
    {
        $sql = 'INSERT INTO productos (nombre, categoria, precio, existencia, imagen, activo)
                VALUES (?, ?, ?, ?, ?, ?)';
        $this->db->prepare($sql)->execute([
            $d['nombre'], $d['categoria'], $d['precio'], $d['existencia'], $d['imagen'],
            $d['activo'] ? 'true' : 'false',   // PostgreSQL espera true o false
        ]);
    }

    // Si no se sube foto nueva, COALESCE conserva la anterior
    public function actualizar(int $id, array $d): void
    {
        $sql = 'UPDATE productos SET
                    nombre = ?, categoria = ?, precio = ?, existencia = ?,
                    imagen = COALESCE(?, imagen),
                    activo = CAST(? AS BOOLEAN)
                WHERE id = ?';
        $this->db->prepare($sql)->execute([
            $d['nombre'], $d['categoria'], $d['precio'], $d['existencia'], $d['imagen'],
            $d['activo'] ? 'true' : 'false', $id,
        ]);
    }

    public function eliminar(int $id): void
    {
        $this->db->prepare('DELETE FROM productos WHERE id = ?')->execute([$id]);
    }

    public function desactivar(int $id): void
    {
        $this->db->prepare('UPDATE productos SET activo = FALSE WHERE id = ?')->execute([$id]);
    }
}
