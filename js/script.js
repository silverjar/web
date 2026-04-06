// js/script.js - Funcionalidades JavaScript

// Confirmar eliminaciones
document.addEventListener('DOMContentLoaded', function() {
    // Botones de eliminar
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if(!confirm('¿Estás seguro de eliminar este registro?')) {
                e.preventDefault();
            }
        });
    });
    
    // Búsqueda en tiempo real para productos
    const searchInput = document.getElementById('searchProduct');
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const items = document.querySelectorAll('.product-item');
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(term) ? 'block' : 'none';
            });
        });
    }
    
    // Validación de formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const required = form.querySelectorAll('[required]');
            let valid = true;
            required.forEach(field => {
                if(!field.value.trim()) {
                    field.style.borderColor = 'red';
                    valid = false;
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            if(!valid) {
                e.preventDefault();
                alert('Por favor completa todos los campos requeridos');
            }
        });
    });
    
    // Mostrar/ocultar contraseña
    const togglePassword = document.querySelector('.toggle-password');
    if(togglePassword) {
        togglePassword.addEventListener('click', function() {
            const password = document.querySelector('#password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    }
    
    // Tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(el => {
        el.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            document.body.appendChild(tooltip);
            const rect = this.getBoundingClientRect();
            tooltip.style.top = rect.top - 30 + 'px';
            tooltip.style.left = rect.left + (rect.width/2) - (tooltip.offsetWidth/2) + 'px';
            this.addEventListener('mouseleave', () => tooltip.remove());
        });
    });
});

// Función para imprimir reportes
function imprimirReporte() {
    window.print();
}

// Función para exportar a CSV
function exportarCSV(data, filename) {
    let csv = '';
    data.forEach(row => {
        csv += row.join(',') + '\n';
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.csv';
    link.click();
}

// Notificaciones
function mostrarNotificacion(mensaje, tipo = 'success') {
    const notif = document.createElement('div');
    notif.className = `notification notification-${tipo}`;
    notif.textContent = mensaje;
    document.body.appendChild(notif);
    setTimeout(() => {
        notif.classList.add('show');
        setTimeout(() => {
            notif.classList.remove('show');
            setTimeout(() => notif.remove(), 300);
        }, 3000);
    }, 100);
}

// Validación de stock en tiempo real
function validarStock(productoId, cantidad) {
    fetch(`check_stock.php?id=${productoId}&cantidad=${cantidad}`)
        .then(response => response.json())
        .then(data => {
            if(!data.disponible) {
                alert(`Stock insuficiente. Disponible: ${data.stock}`);
                return false;
            }
            return true;
        });
}

// Actualizar total del carrito
function actualizarTotal() {
    let total = 0;
    document.querySelectorAll('.cart-item-subtotal').forEach(el => {
        total += parseFloat(el.textContent);
    });
    document.getElementById('cartTotal').textContent = total.toFixed(2);
}

// Estilos adicionales para notificaciones
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        z-index: 1000;
    }
    .notification.show {
        transform: translateX(0);
    }
    .notification-success {
        border-left: 4px solid #28a745;
        background: #d4edda;
        color: #155724;
    }
    .notification-error {
        border-left: 4px solid #dc3545;
        background: #f8d7da;
        color: #721c24;
    }
    .tooltip {
        position: fixed;
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
    }
    .badge.admin {
        background: #667eea;
        color: white;
    }
    .badge.vendedor {
        background: #28a745;
        color: white;
    }
    .badge.success {
        background: #28a745;
        color: white;
    }
    .badge.info {
        background: #17a2b8;
        color: white;
    }
    .report-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .filter-form {
        margin-bottom: 20px;
    }
    .report-summary {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    @media print {
        .sidebar, .header a, .report-tabs, .filter-form {
            display: none;
        }
        .main-content {
            margin-left: 0;
            padding: 0;
        }
        .card {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
`;
document.head.appendChild(style);