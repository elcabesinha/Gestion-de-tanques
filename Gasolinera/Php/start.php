<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Gestión de Tanques</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>
<div class="container-fluid px-0">
    <div class="sena-header py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <svg class="sena-logo" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="60" fill="#004C6E" rx="8"/>
                <text x="15" y="40" font-family="Arial" font-weight="bold" font-size="28" fill="#07ff30">Estación</text>
                <text x="90" y="55" font-family="Arial" font-size="16" fill="white">Sena</text>
                <circle cx="175" cy="30" r="12" fill="#55ff07"/>
                <text x="164" y="37" font-family="Arial" font-size="16" fill="#004C6E" font-weight="bold">⛽</text>
            </svg>
            <h2 class="h4 mb-0 fw-bold">Gestión de Tanques</h2>
        </div>
        <span class="badge bg-warning text-dark">Sistema SENA</span>
    </div>

    <div class="container my-4">
        <!-- Formularios -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-bold">➕ Nuevo Tanque</div>
                    <div class="card-body">
                        <form id="formAddTank">
                            <input type="text" id="tankName" class="form-control mb-2" placeholder="Nombre del tanque" required>
                            <select id="fuelType" class="form-select mb-2" required>
                                <option value="Gasolina Corriente">Gasolina Corriente</option>
                                <option value="Gasolina Extra">Gasolina Extra</option>
                                <option value="A.C.P.M">A.C.P.M (Diésel)</option>
                            </select>
                            <input type="number" id="capacity" class="form-control mb-2" placeholder="Capacidad (galones)" min="1" required>
                            <input type="number" id="initialGallons" class="form-control mb-3" placeholder="Galones iniciales" min="0" required>
                            <button type="submit" class="btn btn-sena w-100">Agregar tanque</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-bold">💰 Registrar Venta</div>
                    <div class="card-body">
                        <form id="formSale">
                            <select id="saleTankId" class="form-select mb-2" required>
                                <option value="">-- Seleccionar tanque --</option>
                            </select>
                            <input type="number" id="saleGallons" class="form-control mb-3" placeholder="Galones vendidos" min="1" required>
                            <button type="submit" class="btn btn-danger w-100">Registrar venta</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-bold">⛽ Recargar Tanque</div>
                    <div class="card-body">
                        <form id="formRefill">
                            <select id="refillTankId" class="form-select mb-2" required>
                                <option value="">-- Seleccionar tanque --</option>
                            </select>
                            <input type="number" id="refillGallons" class="form-control mb-3" placeholder="Galones a agregar" min="1" required>
                            <button type="submit" class="btn btn-primary w-100">Realizar recarga</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tanques dinámicos -->
        <div class="mb-5">
            <div class="d-flex justify-content-between">
                <h4><i class="bi bi-fuel-pump-fill"></i> Estado de tanques</h4>
                <button class="btn btn-sm btn-outline-secondary" id="refreshBtn">Actualizar</button>
            </div>
            <div id="tanksContainer" class="row g-4 mt-2">
                <div class="col-12 text-center py-5">Cargando...</div>
            </div>
        </div>

        <!-- Historiales -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">Últimas ventas</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><th>Tanque</th><th>Galones</th><th>Fecha</th> </thead>
                            <tbody id="salesTable"><td colspan="3">Cargando...</td> </tbody>
                         </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">Últimas recargas</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><th>Tanque</th><th>Galones</th><th>Fecha</th> </thead>
                            <tbody id="refillsTable"><td colspan="3">Cargando...</td> </tbody>
                         </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="text-center py-3 bg-light small">SENA - Sistema de control de inventario de combustible</footer>
</div>

<!-- Modal de edición de tanque -->
<div class="modal fade" id="editTankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Tanque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditTank">
                    <input type="hidden" id="editTankId">
                    <div class="mb-2">
                        <label>Nombre del tanque</label>
                        <input type="text" id="editTankName" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Tipo de combustible</label>
                        <select id="editFuelType" class="form-select" required>
                            <option value="Gasolina Corriente">Gasolina Corriente</option>
                            <option value="Gasolina Extra">Gasolina Extra</option>
                            <option value="A.C.P.M">A.C.P.M (Diésel)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Capacidad (galones)</label>
                        <input type="number" id="editCapacity" class="form-control" min="1" required>
                        <small class="text-muted">La capacidad no puede ser menor a los galones actuales</small>
                    </div>
                    <button type="submit" class="btn btn-sena w-100">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const API_BASE = 'api.php';
    let editModal; // variable para controlar el modal

    async function fetchAPI(action, method = 'GET', body = null) {
        const options = { method, headers: { 'Content-Type': 'application/json' } };
        if (body) options.body = JSON.stringify(body);
        const res = await fetch(`${API_BASE}?action=${action}`, options);
        return await res.json();
    }

    async function loadTanks() {
        const data = await fetchAPI('get_tanks');
        if (data.success) {
            renderTanks(data.tanks);
            updateSelectors(data.tanks);
        } else {
            document.getElementById('tanksContainer').innerHTML = `<div class="col-12 alert alert-danger">Error: ${data.error}</div>`;
        }
    }

    function renderTanks(tanks) {
        const container = document.getElementById('tanksContainer');
        if (!tanks.length) {
            container.innerHTML = '<div class="col-12 alert alert-info">No hay tanques registrados.</div>';
            return;
        }
        container.innerHTML = tanks.map(tank => {
            const percentage = tank.porcentaje;
            const isLow = tank.alerta;
            let liquidClass = '';
            if (percentage <= 30) liquidClass = 'low';
            if (percentage <= 10) liquidClass = 'danger';
            
            return `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="tank-card" data-id="${tank.id}">
                        <div class="tank-actions">
                            <button class="btn-edit" onclick="openEditModal(${tank.id}, '${escapeHtml(tank.nombre)}', '${escapeHtml(tank.tipo_combustible)}', ${tank.capacidad})"><i class="bi bi-pencil"></i> Editar</button>
                            <button class="btn-delete" onclick="deleteTank(${tank.id})"><i class="bi bi-trash"></i> Eliminar</button>
                        </div>
                        <div class="tank-cap"></div>
                        <div class="tank-liquid ${liquidClass}" style="height: ${percentage}%;"></div>
                        <div class="tank-info">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="tank-name">${escapeHtml(tank.nombre)}</div>
                                <div class="tank-fuel-type">${escapeHtml(tank.tipo_combustible)}</div>
                            </div>
                            <div class="tank-stats">
                                <div><i class="bi bi-speedometer2"></i> Capacidad: <span>${tank.capacidad} gal</span></div>
                                <div><i class="bi bi-fuel-pump"></i> Actual: <span>${tank.galones_actuales} gal</span></div>
                                <div><i class="bi bi-pie-chart"></i> ${percentage}%</div>
                            </div>
                            ${isLow ? '<div class="alert-badge"><i class="bi bi-exclamation-triangle-fill"></i> ALERTA: 30% o menos</div>' : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function updateSelectors(tanks) {
        const saleSelect = document.getElementById('saleTankId');
        const refillSelect = document.getElementById('refillTankId');
        const options = tanks.map(t => `<option value="${t.id}">${escapeHtml(t.nombre)} (${t.galones_actuales}/${t.capacidad} gal) - ${escapeHtml(t.tipo_combustible)}</option>`).join('');
        saleSelect.innerHTML = '<option value="">-- Seleccionar tanque --</option>' + options;
        refillSelect.innerHTML = '<option value="">-- Seleccionar tanque --</option>' + options;
    }

    async function loadRecentSales() {
        const data = await fetchAPI('recent_sales');
        const tbody = document.getElementById('salesTable');
        if (data.success && data.sales.length) {
            tbody.innerHTML = data.sales.map(s => `<tr><td>${escapeHtml(s.tanque_nombre)}</td><td>${s.galones_vendidos}</td><td>${new Date(s.fecha).toLocaleString()}</td></tr>`).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="3">No hay ventas registradas</td></tr>';
        }
    }

    async function loadRecentRefills() {
        const data = await fetchAPI('recent_refills');
        const tbody = document.getElementById('refillsTable');
        if (data.success && data.refills.length) {
            tbody.innerHTML = data.refills.map(r => `<tr><td>${escapeHtml(r.tanque_nombre)}</td><td>${r.galones_agregados}</td><td>${new Date(r.fecha).toLocaleString()}</td></tr>`).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="3">No hay recargas registradas</td></tr>';
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // ABRIR MODAL DE EDICIÓN
    window.openEditModal = function(id, nombre, tipo, capacidad) {
        document.getElementById('editTankId').value = id;
        document.getElementById('editTankName').value = nombre;
        document.getElementById('editFuelType').value = tipo;
        document.getElementById('editCapacity').value = capacidad;
        editModal.show();
    }

    // ENVIAR EDICIÓN
    document.getElementById('formEditTank').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editTankId').value;
        const nombre = document.getElementById('editTankName').value.trim();
        const tipo = document.getElementById('editFuelType').value;
        const capacidad = parseInt(document.getElementById('editCapacity').value);
        if (!nombre || !tipo || isNaN(capacidad) || capacidad <= 0) {
            alert('Complete todos los campos correctamente');
            return;
        }
        const res = await fetchAPI('update_tank', 'POST', { id, nombre, tipo_combustible: tipo, capacidad });
        if (res.success) {
            alert('Tanque actualizado correctamente');
            editModal.hide();
            loadTanks();
            loadRecentSales();
            loadRecentRefills();
        } else {
            alert('Error: ' + res.error);
        }
    });

    // ELIMINAR TANQUE
    window.deleteTank = async function(id) {
        if (!confirm('¿Estás seguro de eliminar este tanque? Se perderán todas las ventas y recargas asociadas.')) return;
        const res = await fetchAPI('delete_tank', 'POST', { id });
        if (res.success) {
            alert('Tanque eliminado');
            loadTanks();
            loadRecentSales();
            loadRecentRefills();
        } else {
            alert('Error: ' + res.error);
        }
    }

    // Eventos de formularios (agregar, venta, recarga)
    document.getElementById('formAddTank').addEventListener('submit', async (e) => {
        e.preventDefault();
        const nombre = document.getElementById('tankName').value.trim();
        const tipo = document.getElementById('fuelType').value;
        const capacidad = parseInt(document.getElementById('capacity').value);
        const galones_iniciales = parseInt(document.getElementById('initialGallons').value);
        if (!nombre || !capacidad || isNaN(galones_iniciales)) return alert('Complete todos los campos');
        const res = await fetchAPI('add_tank', 'POST', { nombre, tipo_combustible: tipo, capacidad, galones_iniciales });
        if (res.success) {
            alert('Tanque agregado exitosamente');
            document.getElementById('formAddTank').reset();
            loadTanks();
            loadRecentSales();
            loadRecentRefills();
        } else {
            alert('Error: ' + res.error);
        }
    });

    document.getElementById('formSale').addEventListener('submit', async (e) => {
        e.preventDefault();
        const tanque_id = document.getElementById('saleTankId').value;
        const galones_vendidos = parseInt(document.getElementById('saleGallons').value);
        if (!tanque_id || isNaN(galones_vendidos) || galones_vendidos <= 0) return alert('Datos inválidos');
        const res = await fetchAPI('add_sale', 'POST', { tanque_id: parseInt(tanque_id), galones_vendidos });
        if (res.success) {
            alert(`Venta registrada. Nuevo nivel: ${res.nuevos_galones} galones`);
            document.getElementById('formSale').reset();
            loadTanks();
            loadRecentSales();
            loadRecentRefills();
        } else {
            alert('Error: ' + res.error);
        }
    });

    document.getElementById('formRefill').addEventListener('submit', async (e) => {
        e.preventDefault();
        const tanque_id = document.getElementById('refillTankId').value;
        const galones_agregados = parseInt(document.getElementById('refillGallons').value);
        if (!tanque_id || isNaN(galones_agregados) || galones_agregados <= 0) return alert('Datos inválidos');
        const res = await fetchAPI('add_refill', 'POST', { tanque_id: parseInt(tanque_id), galones_agregados });
        if (res.success) {
            alert(`Recarga exitosa. Nuevos galones: ${res.nuevos_galones}`);
            document.getElementById('formRefill').reset();
            loadTanks();
            loadRecentSales();
            loadRecentRefills();
        } else {
            alert('Error: ' + res.error);
        }
    });

    document.getElementById('refreshBtn').addEventListener('click', () => {
        loadTanks();
        loadRecentSales();
        loadRecentRefills();
    });

    // Inicializar modal
    document.addEventListener('DOMContentLoaded', () => {
        editModal = new bootstrap.Modal(document.getElementById('editTankModal'));
        loadTanks();
        loadRecentSales();
        loadRecentRefills();
    });
</script>
</body>
</html>