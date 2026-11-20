<?php
class ProductoController extends Controller
{
    private Producto $productos;

    public function __construct(PDO $db)
    {
        $this->productos = new Producto($db);
    }

    public function menu(): void
    {
        $usuario = $this->exigirSesion();
        $esAdmin = $usuario['rol'] === 'admin';
        $productos = $this->productos->todos(!$esAdmin);
        $categorias = Producto::CATEGORIAS;
        $horarios = Pedido::HORARIOS;
        require __DIR__ . '/../views/productos/menu.php';
    }

    public function formulario(int $id = 0): void
    {
        $this->exigirAdmin();
        $producto = $id ? $this->productos->buscarPorId($id) : null;
        $categorias = Producto::CATEGORIAS;
        $error = null;
        require __DIR__ . '/../views/productos/formulario.php';
    }

    public function guardar(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $precio = $_POST['precio'] ?? '';
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'categoria' => $_POST['categoria'] ?? '',
            'precio' => is_numeric($precio) ? (float) $precio : -1,
            'existencia' => (int) ($_POST['existencia'] ?? -1),
            'activo' => isset($_POST['activo']),
            'imagen' => null,
        ];

        try {
            if (!preg_match('/^.{3,80}$/u', $datos['nombre'])) {
                throw new InvalidArgumentException('El nombre debe tener entre 3 y 80 caracteres.');
            }
            if (!in_array($datos['categoria'], Producto::CATEGORIAS, true)) {
                throw new InvalidArgumentException('Elige una categoría de la lista.');
            }
            if ($datos['precio'] < 0) {
                throw new InvalidArgumentException('Escribe un precio válido.');
            }
            if ($datos['existencia'] < 0 || $datos['existencia'] > 999) {
                throw new InvalidArgumentException('La existencia debe estar entre 0 y 999 piezas.');
            }

            $datos['imagen'] = $this->subirImagen();

            if ($id) {
                $this->productos->actualizar($id, $datos);
            } else {
                $this->productos->crear($datos);
            }
            $this->redirigir('', 'Producto guardado.');
        } catch (InvalidArgumentException $ex) {
            // Vuelve a mostrar el formulario con lo que escribió y el error
            $error = $ex->getMessage();
            $producto = ['id' => $id] + $datos;
            $categorias = Producto::CATEGORIAS;
            require __DIR__ . '/../views/productos/formulario.php';
        }
    }

    // Valida y guarda la foto; devuelve el nombre del archivo o null si no se subió
    private function subirImagen(): ?string
    {
        $archivo = $_FILES['imagen'] ?? null;
        if (!$archivo || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se pudo subir la imagen.');
        }
        if ($archivo['size'] > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('La imagen debe pesar menos de 2 MB.');
        }

        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $tipo = mime_content_type($archivo['tmp_name']);   // revisa el contenido real, no el nombre
        if (!isset($permitidos[$tipo])) {
            throw new InvalidArgumentException('Solo se aceptan imágenes JPG, PNG o WEBP.');
        }

        $nombre = uniqid('producto_') . '.' . $permitidos[$tipo];
        if (!move_uploaded_file($archivo['tmp_name'], __DIR__ . '/../uploads/' . $nombre)) {
            throw new InvalidArgumentException('No se pudo guardar la imagen en el servidor.');
        }
        return $nombre;
    }

    public function eliminar(): void
    {
        $this->exigirAdmin();
        if (!$this->esPost()) {
            $this->redirigir();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $producto = $this->productos->buscarPorId($id);
        if (!$producto) {
            $this->redirigir();
        }

        try {
            $this->productos->eliminar($id);
            $ruta = __DIR__ . '/../uploads/' . $producto['imagen'];
            if ($producto['imagen'] && is_file($ruta)) {
                unlink($ruta);
            }
            $this->redirigir('', 'Producto eliminado.');
        } catch (PDOException $ex) {
            // 23503: la llave foránea impide borrar un producto que ya aparece en pedidos
            if ($ex->getCode() !== '23503') {
                throw $ex;
            }
            $this->productos->desactivar($id);
            $this->redirigir('', 'El producto ya aparece en pedidos, así que se ocultó en lugar de borrarse.');
        }
    }
}
