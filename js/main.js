function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 *
 * @param {HTMLTextAreaElement} textarea
 */
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight) + 'px';
    textarea.style.overflowY = 'hidden';
}
const Buscador = {
    controller: null,
    timer: null,
    ultimaBusqueda: '',
    input: null,
    sugerenciasBox: null,

    init() {
        this.input = document.getElementById('input-busqueda');
        this.sugerenciasBox = document.getElementById('sugerencias');

        if (!this.input || !this.sugerenciasBox) return;

        this.input.addEventListener('input', (e) => this.manejarInput(e));

        this.input.addEventListener('keydown', (e) => this.manejarTeclado(e));
        
        this.input.addEventListener('focus', () => {
            if (this.sugerenciasBox.children.length > 0) {
                this.sugerenciasBox.style.display = 'block';
            }
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.buscador-container')) {
                this.sugerenciasBox.style.display = 'none';
            }
        });
    },

    manejarInput(e) {
        const texto = e.target.value.trim();

        if (this.controller) this.controller.abort();
        clearTimeout(this.timer);

        if (texto.length < 2) {
            this.sugerenciasBox.style.display = 'none';
            this.input.classList.remove('buscando');
            return;
        }

        if (texto === this.ultimaBusqueda) return;

        this.input.classList.add('buscando');
        const delay = texto.length < this.ultimaBusqueda.length ? 100 : 300;
        this.ultimaBusqueda = texto;

        this.timer = setTimeout(() => this.buscar(texto), delay);
    },

    async buscar(texto) {
        this.controller = new AbortController();

        try {
            const resp = await fetch(
                `./php/buscar_sugerencias.php?q=${encodeURIComponent(texto)}&limit=8`,
                {
                    signal: this.controller.signal,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }
            );

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

            const data = await resp.json();
            if (data.error) throw new Error(data.error);

            this.renderizar(data);

        } catch (error) {
            if (error.name === 'AbortError') return;

            console.error("Error búsqueda:", error);
            this.sugerenciasBox.innerHTML = `
                <div class="sugerencia-error">
                    ⚠️ Error al buscar. Intenta de nuevo.
                </div>`;
            this.sugerenciasBox.style.display = 'block';

        } finally {
            this.input?.classList.remove('buscando');
        }
    },

    renderizar(juegos) {
        if (!juegos || juegos.length === 0) {
            this.sugerenciasBox.innerHTML = `
                <div class="sugerencia-vacio">
                    🔍 No se encontraron juegos
                </div>`;
            this.sugerenciasBox.style.display = 'block';
            return;
        }

        this.sugerenciasBox.innerHTML = juegos.map(juego => {
            const img = juego.cover
                ? juego.cover.replace('t_thumb', 't_cover_small')
                : 'https://via.placeholder.com/40x50?text=?';

            const rating = juego.rating
                ? `<span class="sugerencia-rating">★ ${(juego.rating / 10).toFixed(1)}</span>`
                : '';

            const year = juego.year
                ? `<span class="sugerencia-year">(${juego.year})</span>`
                : '';

            return `
                <div class="sugerencia-item" data-id="${juego.id}" role="option">
                    <img src="${img}" alt="" loading="lazy" onerror="this.src='https://via.placeholder.com/40x50?text=?'">
                    <div class="sugerencia-info">
                        <span class="sugerencia-nombre">${escapeHtml(juego.name)} ${year}</span>
                        ${rating}
                    </div>
                </div>
            `;
        }).join('');

        this.sugerenciasBox.style.display = 'block';

        this.sugerenciasBox.querySelectorAll('.sugerencia-item').forEach(item => {
            item.addEventListener('click', () => {
                window.location.href = `juego.php?id=${item.dataset.id}`;
            });
        });
    },

    manejarTeclado(e) {
        const items = this.sugerenciasBox.querySelectorAll('.sugerencia-item');
        const activo = this.sugerenciasBox.querySelector('.sugerencia-item.active');
        let index = Array.from(items).indexOf(activo);

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (activo) activo.classList.remove('active');
                index = (index + 1) % items.length;
                items[index]?.classList.add('active');
                items[index]?.scrollIntoView({ block: 'nearest' });
                break;

            case 'ArrowUp':
                e.preventDefault();
                if (activo) activo.classList.remove('active');
                index = index <= 0 ? items.length - 1 : index - 1;
                items[index]?.classList.add('active');
                items[index]?.scrollIntoView({ block: 'nearest' });
                break;

            case 'Enter':
                e.preventDefault();
                const seleccionado = activo || items[0];
                if (seleccionado) {
                    window.location.href = `juego.php?id=${seleccionado.dataset.id}`;
                }
                break;

            case 'Escape':
                this.sugerenciasBox.style.display = 'none';
                this.input.blur();
                break;
        }
    }
};

const Perfil = {
    init() {
        // Solo ejecutar si estamos en la página de perfil
        const formBio = document.getElementById('form-bio');
        if (!formBio) return;

        this.inicializarBotones();
        this.inicializarContador();
    },

inicializarBotones() {
    const btnEditar = document.getElementById('btn-editar-bio');
    const btnCancelar = document.querySelector('#form-bio .btn-cancelar');

    if (btnEditar) {
        btnEditar.addEventListener('click', () => this.toggleEditar(true));
    }

    if (btnCancelar) {
        btnCancelar.addEventListener('click', () => this.toggleEditar(false));
    }
},

    toggleEditar(mostrar) {
        const txtBio = document.getElementById('txt-bio');
        const formBio = document.getElementById('form-bio');
        const btnEditar = document.getElementById('btn-editar-bio');

        if (!txtBio || !formBio || !btnEditar) {
            console.error("Elementos del perfil no encontrados");
            return;
        }

        if (mostrar) {
            txtBio.classList.add('oculto');
            formBio.classList.remove('oculto');
            btnEditar.classList.add('oculto');
            
            // Ajustar altura del textarea al mostrar
            const textarea = formBio.querySelector('textarea');
            if (textarea) {
                textarea.focus();
                autoResizeTextarea(textarea);
            }
        } else {
            txtBio.classList.remove('oculto');
            formBio.classList.add('oculto');
            btnEditar.classList.remove('oculto');
        }
    },

    inicializarContador() {
        const textarea = document.querySelector('.textarea-bio');
        const charCount = document.getElementById('char-count');

        if (!textarea || !charCount) return;

        charCount.textContent = textarea.value.length;

        textarea.addEventListener('input', function () {
            charCount.textContent = this.value.length;
            charCount.style.color = this.value.length >= 255 ? '#e74c3c' : 'inherit';
        });
    }
};

/*SCROLL*/
const Historial = {
    init() {
        const tabs = document.querySelectorAll('.perfil-tabs .tab-item');
        const contenedor = document.getElementById('historial-ajax');

        if (!tabs.length || !contenedor) return;

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const url = tab.getAttribute('href');

                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                contenedor.style.opacity = '0.4';

                fetch(url)
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const nuevoContenido = doc.getElementById('historial-ajax').innerHTML;

                        contenedor.innerHTML = nuevoContenido;
                        contenedor.style.opacity = '1';

                        window.history.pushState(null, '', url);
                    })
                    .catch(err => {
                        console.error("Error historial:", err);
                        contenedor.style.opacity = '1';
                    });
            });
        });
    }
};

const Textareas = {
    init() {
        const comentarioArea = document.getElementById('comentario-area');
        if (comentarioArea) {
            this.configurar(comentarioArea);
        }

        const bioArea = document.querySelector('.textarea-bio');
        if (bioArea) {
            this.configurar(bioArea);
        }
    },

    configurar(textarea) {
        autoResizeTextarea(textarea);

        textarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
    }
};

const Utilidades = {

    autoHideMessages() {
        setTimeout(() => {
            const mensajes = document.querySelectorAll('.mensaje-exito, .mensaje-error');
            mensajes.forEach(msg => {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            });
        }, 3000);
    }
};

const body = `fields name, cover.url, first_release_date; 
              where first_release_date > ${Math.floor(Date.now() / 1000)} & cover != null; 
              sort first_release_date asc; 
              limit 12;`;

const Logros = {
    init() {
        const logroData = document.getElementById('logro-data');
        
        if (logroData) {
            const nombreLogro = logroData.dataset.nombre;
            this.mostrar(nombreLogro);
        }
    },

    mostrar(nombre) {
        const alerta = document.createElement('div');
        alerta.className = 'alerta-logro';
        alerta.innerHTML = `
            <div class="logro-icono">🏆</div>
            <div class="logro-texto">
                <strong>¡LOGRO DESBLOQUEADO!</strong>
                <p>${escapeHtml(nombre)}</p>
            </div>
        `;

        document.body.appendChild(alerta);

        setTimeout(() => {
            alerta.classList.add('saliendo');
            setTimeout(() => alerta.remove(), 500);
        }, 4000);
    }
};

const Juego = {
    init() {
        const selectEstado = document.getElementById('estado-juego');
        if (!selectEstado) return;
        
        selectEstado.addEventListener('change', () => this.verificarEstado());
        this.verificarEstado();
    },

    verificarEstado() {
        const select = document.getElementById('estado-juego');
        const seccionPuntuacion = document.getElementById('seccion-puntuacion');
        const seccionResena = document.querySelector('.seccion-resena');

        if (!select) return;
        if (select.value === 'Pendiente') {
            if (seccionPuntuacion) seccionPuntuacion.style.display = 'none';
        } else {
            if (seccionPuntuacion) seccionPuntuacion.style.display = 'block';
            if (seccionResena) seccionResena.style.display = 'block';
        }
    }
};

function verificarEstado() {
    Juego.verificarEstado();
}
document.addEventListener('DOMContentLoaded', () => {
    Buscador.init();

    Perfil.init();

    Historial.init(); 

    Textareas.init();

    Logros.init()

    Juego.init();

    Utilidades.autoHideMessages();
});
