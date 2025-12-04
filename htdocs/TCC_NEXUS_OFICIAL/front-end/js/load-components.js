// Arquivo: /js/load-components.js

console.log('teste')

document.addEventListener('DOMContentLoaded', () => {
    
    // --- Funções Auxiliares para Carregar Componentes ---

    function loadComponent(url, containerSelector, position = 'afterbegin', callback = null) {
        const container = document.querySelector(containerSelector);
        
        if (container) {
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status} for ${url}`);
                    }
                    return response.text();
                })
                .then(html => {
                    container.insertAdjacentHTML(position, html);
                    if (callback) {
                        callback();
                    }
                })
                .catch(error => console.error(`Erro ao carregar componente (${url}):`, error));
        } else {
            console.warn(`Container não encontrado para ${url}: ${containerSelector}`);
        }
    }

    // --- Injeção de Componentes (Ordem Importante) ---
    
    // 1. Injeta o Menu Lateral (Sidebar) dentro do .dashboard-layout
    loadComponent('menu-sidebar.php', '.dashboard-layout', 'afterbegin', () => {
        // Callback: Após o menu ser injetado, destacamos o item ativo.
        highlightActiveMenuItem();
    });

    // 2. Injeta o Header Fixo (Header) dentro do .main-content
    // Usamos 'afterbegin' para que ele seja o primeiro elemento dentro do main-content.
    loadComponent('main-header.php', '.main-content', 'afterbegin');
});

// --- Função para Destacar o Item Ativo (Sem Alterações) ---
function highlightActiveMenuItem() {
    const currentPagePath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.menu-list a');

    menuLinks.forEach(link => {
        const linkPath = link.getAttribute('href').split('/').pop();
        
        if (currentPagePath.endsWith(linkPath)) {
            // Remove ativos e adiciona ao correto
            document.querySelectorAll('.active-link-item').forEach(el => el.classList.remove('active-link-item'));
            link.classList.add('active-link-item');
        }
    });
}