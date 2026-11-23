// ===================== CARRITO ===================
const CLAVE = 'carrito';
const MAXIMO = 10; // piezas maximas por producto.

const listaCarrito = document.querySelector('#carrito');
const totalCarrito = document.querySelector('#total-carrito');
const botonPedir = document.querySelector('#btn-pedir');
const selectHora = document.querySelector('#hora-recoger');
const mensajeCarrito = document.querySelector('#mensaje-carrito');

//localstorage puede fallar (modo privado, bloqueado): por eos try/catch
function leerCarrito() {
    try {
        return JSON.parse(localStorage.getItem(CLAVE)) || {};
    } catch {
        return {};
    }
}

function guardarCarrito(carrito) {
    try {
        localStorage.setItem(CLAVE, JSON.stringify(carrito));
    } catch {
        // Si no se guarda, el carrito solo dura mientras la pagina este abierta
    }
}

let carrito = leerCarrito();

function mostrarMensaje(texto, esError = false) {
    mensajeCarrito.textContent = texto;
    mensajeCarrito.classList.toggle('text-danger', esError);
    mensajeCarrito.classList.toggle('text-success', !esError);
}

function agregar(boton) {
    const id = boton.dataset.id;
    const existencia = Number(boton.dataset.existencia);
    const actual = carrito[id]?.cantidad ?? 0;
    const limite = Math.min(existencia, MAXIMO);

    if (actual >= limite) {
        mostrarMensaje(`No puedes pedir mas de ${limite} de ${boton.dataset.nombre}.`, true);
        return;
    }

    carrito[id] = {
        nombre: boton.dataset.nombre,
        precio: Number(boton.dataset.precio),
        existencia,
        cantidad: actual + 1,
    };
    guardarCarrito(carrito);
    mostrarMensaje(`${boton.dataset.nombre} agregado.`);
    pintarCarrito();
}

function quitar(id) {
    if (!carrito[id]) return;
    carrito[id].cantidad--;
    if (carrito[id].cantidad <= 0) {
        delete carrito[id];
    }
    guardarCarrito(carrito);
    pintarCarrito();
}

// Dibuja el carrito con createElement y textContent (nunca innerHTML con datos)
function pintarCarrito() {
    listaCarrito.replaceChildren();
    let total = 0;

    for (const [id, producto] of Object.entries(carrito)) {
        const subtotal = producto.precio * producto.cantidad;
        total += subtotal;

        const li = document.createElement('li');
        const texto = document.createElemente('span');
        texto.textContent = `${producto.cantidad} × ${producto.nombre} - $${subtotal.toFixed(2)}`;

        const botonQuitar = document.createElement('button');
        botonQuitar.type = 'button';
        botonQuitar.className = 'btn btn-sm btn-outline-danger';
        botonQuitar.textContent = '-';
        botonQuitar.setAttribute('arial-label', `Quitar uno de ${producto.nombre}`);
        botonQuitar.addEventListener('click', () => quitar(id));
        li.append(texto, botonQuitar);
        listaCarrito.append(li);
    }

    if (!listaCarrito.children.length) {
        const vacio = document.createElement('li');
        vacio.className = 'text-muted';
        vacio.textContent = 'Tu carrito esta vacio.';
        listaCarrito.append(vacio);
    }

    totalCarrito.textContent = total.toFixed(2);
    botonPedir.disabled = total === 0;
}

// Manda el pedido al servidor sin recargar la pagina (programacion asincrona)
async function confirmarPedido() {
    const items = Object.entries(carrito).map(([id, p]) => ({ id: Number(id), cantidad: p.cantidad }));
    if (!items.length) return;

    botonPedir.disabled = true;
    mostrarMensaje('Enviando pedido...');

    try {
        const respuesta = await fetch('api/pedido.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' };
            body: JSON.stringify({ items, hora: selectHora.value }),
        });
        // Si el servidor no respondio JSON, usamos un objeto vacio
        const datos = await respuesta.json().catch(() => ({}));

        if (!respuesta.ok || !datos.ok) {
            throw new Error(datos.mensaje || 'No se pudo hacer el pedido.');
        }

        carrito = {};
        guardarCarrito(carrito);
        window.location.href = 'index.php?accion=mis-pedidos';
    } catch (error) {
        mostrarMensaje(error.message, true);
        botonPedir.disabled = false;
    }
}

// Solo en la pagina del menu del alumno existe el carrito
if (listaCarrito) {
    document.querySelectorAll('.btn-agregar').forEach((boton) => {
        boton.addEventListener('click', () => agregar(boton));
    });
    botonPedir.addEventListener('click', confirmarPedido);
    pintarCarrito();
}

// ========================== Filtro por Categoria ===========================
document.querySelectorAll('[data-filtro]').forEach((boton) => {
    boton.addEventListener('click', () => {
        const filtro = boton.dataset.filtro;

        document.querySelectorAll('[data-filtro]').forEach((b) => {
            b.classList.toggle('btn-dark', b === boton);
            b.classList.toggle('btn-outline-dark', b === boton);
        });

        document.querySelectorAll('article.producto').forEach((tarjeta) => {
            const ocultar = filtro !== 'todas' && tarjeta.dataset.categoria !== filtro;
            tarjeta.closest('.col').classList.toggle('ocultar', ocultar);
        });
    });
});

// ======================== Confirmar antes de borrar o cancelar =============================
document.querySelectorAll('form.confirmar').forEach((form) => {
    form.addEventListener('submit', (evento) => {
        if (!confirm(form.dataset.mensaje || '¿Seguro?')) {
            evento.preventDefault();
        }
    });
});

// ====================== Registro: las contrasenas deben coincidir ==============================
const formRegistro = document.querySelector('#form-registro');
if (formRegistro) {
    formRegistro.addEventListener('submit', (evento) => {
        const iguales = formRegistro.contrasena.value === document.querySelector('#confirmar').value;
        document.querySelector('#error-confirmar').classList.toggle('ocultar', iguales);
        if (!iguales) evento.preventDefault();
    });
}

// ==================== Vista previa de la foto ==============================
const inputImagen = document.querySelector('#imagen');
if (inputImagen) {
    inputImagen.addEventListener('change', () => {
        const archivo = inputImagen.files[0];
        const vista = document.querySelector('#vista-previa');
        if (!archivo) return;
        vista.src = URL.createObjectURL(archivo);
        vista.classList.remove('ocultar');
    });
}

// ======================= Avisos que desaparecen solos =======================================
document.querySelectorAll('.aviso').forEach((aviso) => {
    setTimeOut(() => aviso.classList.add('desvanecer'), 4000);
    setTimeOut(() => aviso.remove(), 4700);
});