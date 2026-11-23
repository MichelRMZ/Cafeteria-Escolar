<?php
class PedidoController extends Controller
{
    private Pedido $pedidos;

    public function __construct(PDO $db)
    {
        $this->pedidos = new Pedido($db);
    }

    public function misPedidos(): void
    {
        $usuario = $this->exigirSesion();
        $pedidos = $this->pedidos->delUsuario($usuario['id']);
        require __DIR__ . '/../views/pedidos/mis_pedidos.php';
    }

    // El alumno solo puede cancelar su pedido mientras esté pendiente
    public function cancelar(): void
    {
        $usuario = $this->exigirSesion();
        if (!$this->esPost()) {
            $this->redirigir('mis-pedidos');
        }

        $pedido = $this->pedidos->buscarPorId((int) ($_POST['id'] ?? 0));
        if (!$pedido || (int) $pedido['usuario_id'] !== $usuario['id'] || $pedido['estado'] !== 'pendiente') {
            $this->redirigir('mis-pedidos', 'Ese pedido ya no se puede cancelar: ya lo están preparando.');
        }
        $this->pedidos->cambiarEstado((int) $pedido['id'], 'cancelado');
        $this->redirigir('mis-pedidos', 'Pedido cancelado.');
    }

    public function panel(): void
    {
        $this->exigirAdmin();
        $pedidos = $this->pedidos->deHoy();
        require __DIR__ . '/../views/pedidos/panel.php';
    }

    public function cambiarEstado(): void
    {
        $this->exigirAdmin();
        $estado = $_POST['estado'] ?? '';
        if (!$this->esPost() || !in_array($estado, ['preparando', 'listo', 'entregado', 'cancelado'], true)) {
            $this->redirigir('panel');
        }

        try {
            $this->pedidos->cambiarEstado((int) ($_POST['id'] ?? 0), $estado);
            $this->redirigir('panel', 'Pedido marcado como ' . $estado . '.');
        } catch (InvalidArgumentException $ex) {
            $this->redirigir('panel', $ex->getMessage());
        }
    }
}
