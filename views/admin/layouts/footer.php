<!-- Modais -->
<?php include 'modals.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>

<!-- Admin Dashboard Scripts -->
<script src="/chamaservico/assets/admin/js/admin-dashboard.js"></script>

<script>
// Inicializar dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Definir página atual baseada na URL ou variável PHP
    const currentPage = '<?= $current_page ?? 'dashboard' ?>';
    
    // Inicializar o dashboard
    window.adminDashboard = new AdminDashboard(currentPage);
    
    // Configurações globais
    window.adminConfig = {
        baseUrl: '/chamaservico',
        apiUrl: '/chamaservico/admin/api',
        refreshInterval: 30000, // 30 segundos
        adminId: <?= $_SESSION['admin_id'] ?? 'null' ?>,
        adminNome: '<?= htmlspecialchars($_SESSION['admin_nome'] ?? '') ?>'
    };
});

// Funções globais para compatibilidade
function carregarConteudo(pagina) {
    if (window.adminDashboard) {
        window.adminDashboard.loadPage(pagina);
    }
}

function atualizarDados() {
    if (window.adminDashboard) {
        window.adminDashboard.refreshData();
    }
}

function logout() {
    Swal.fire({
        title: 'Sair do Sistema?',
        text: "Tem certeza que deseja encerrar sua sessão?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, sair!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Saindo...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirect após delay
            setTimeout(() => {
                window.location.href = '/chamaservico/admin/logout';
            }, 1000);
        }
    });
}

// Utility functions
function showToast(message, type = 'success') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: type,
        title: message
    });
}

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    return new Date(date).toLocaleDateString('pt-BR', { ...defaultOptions, ...options });
}

// Error handling global
window.addEventListener('error', function(e) {
    console.error('Erro JavaScript:', e.error);
    
    if (window.adminConfig?.debug) {
        showToast('Erro interno detectado. Verifique o console.', 'error');
    }
});

// Interceptar erros de fetch
const originalFetch = window.fetch;
window.fetch = function(...args) {
    return originalFetch.apply(this, args)
        .catch(error => {
            console.error('Erro de rede:', error);
            showToast('Erro de conexão. Verifique sua internet.', 'error');
            throw error;
        });
};
</script>
