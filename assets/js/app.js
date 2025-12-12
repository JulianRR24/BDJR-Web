import { cart } from './cart.js';

// Sidebar Navigation Structure
const menuItems = [
    { title: 'Main', id: 'main', items: [
        { label: 'Dashboard', icon: '🏠', link: 'index.html' },
        { label: 'Catalog', icon: '🛍️', link: 'catalog.html' },
        { label: 'Cart', icon: '🛒', link: 'cart.html' },
        { label: 'Contact', icon: '✉️', link: 'contact.html' },
    ]},
    { title: 'Account', id: 'account', items: [
        { label: 'Profile', icon: '👤', link: 'login.html' },
        { label: 'Admin Productos', icon: '📦', link: 'admin_products.html' }, // New Link
        { label: 'Settings', icon: '⚙️', link: 'settings.html' },
    ]},
    { title: 'Basics (Academic)', id: 'basics', items: [
        { label: 'Menu Basics', icon: '📚', link: 'basics/index.html' },
        { label: 'Typography', icon: '✍️', link: 'basics/typography.html' },
        { label: 'Lists', icon: '📝', link: 'basics/lists.html' },
        { label: 'Multimedia', icon: '🎬', link: 'basics/multimedia.html' },
        { label: 'Tables', icon: '📊', link: 'basics/tables.html' },
        { label: 'Forms', icon: '📋', link: 'basics/forms.html' },
    ]}
];

const renderSidebar = () => {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    const path = window.location.pathname;
    const isBasics = path.includes('/basics/');

    // Helper to fix paths
    const getLink = (link) => {
        if (isBasics && !link.startsWith('../')) {
            if (link.startsWith('basics/')) return link.replace('basics/', '');
            return '../' + link;
        }
        return link;
    };

    const navHTML = menuItems.map(group => {
        // Check if any item in this group is active to expand it by default
        const isGroupActive = group.items.some(item => {
            const link = getLink(item.link);
            // Simple check: if current path ends with the link
            // Note: this logic is a bit loose but works for this structure
            return path.endsWith(item.link) || (item.link === 'index.html' && (path.endsWith('/') || path.endsWith('DashboardApp/')));
        });

        return `
        <div class="nav-group">
            <button class="nav-title-btn ${isGroupActive ? 'active' : ''}" onclick="toggleGroup('${group.id}')">
                <span>${group.title}</span>
                <span class="arrow">▼</span>
            </button>
            <div class="nav-items ${isGroupActive ? 'expanded' : ''}" id="group-${group.id}">
                ${group.items.map(item => {
                    let link = getLink(item.link);
                    const isActive = path.endsWith(item.link) || (item.link === 'index.html' && (path.endsWith('/') || path.endsWith('DashboardApp/')));
                    
                    return `
                        <a href="${link}" class="nav-link ${isActive ? 'active' : ''}">
                            <span>${item.icon}</span>
                            <span>${item.label}</span>
                        </a>
                    `;
                }).join('')}
            </div>
        </div>
    `}).join('');

    sidebarContainer.innerHTML = `
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="brand">BDJR<span style="color:var(--accent)">.co</span></div>
            </div>
            <nav class="sidebar-nav">
                ${navHTML}
            </nav>
            <div class="sidebar-footer">
                <div class="text-muted" style="font-size: 0.75rem;">© 2025 BDJR</div>
            </div>
        </aside>
        
        <!-- Mobile Toggle -->
        <button class="menu-toggle" id="menu-toggle">
            ☰
        </button>
    `;

    // Mobile Toggle Logic
    const toggleBtn = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
        
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target) && 
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Update Auth Links
    import('./auth.js').then(({ isAuthenticated, getCurrentUser, logout }) => {
        if (isAuthenticated()) {
            const user = getCurrentUser();
            const profileLinks = document.querySelectorAll('a[href="login.html"]');
            profileLinks.forEach(link => {
                link.href = 'dashboard.html';
                link.innerHTML = `<span>👤</span><span>Mi Cuenta</span>`;
            });
            
            // Add Logout Button to Sidebar Footer
            const footer = document.querySelector('.sidebar-footer');
            if (footer) {
                const logoutBtn = document.createElement('button');
                logoutBtn.className = 'btn btn-sm btn-outline-danger w-full mt-2';
                logoutBtn.textContent = 'Cerrar Sesión';
                logoutBtn.onclick = logout;
                footer.insertBefore(logoutBtn, footer.firstChild);
            }
        }
    });
};

// Expose toggle function globally
window.toggleGroup = (id) => {
    const group = document.getElementById(`group-${id}`);
    const btn = group.previousElementSibling;
    
    if (group.classList.contains('expanded')) {
        group.classList.remove('expanded');
        btn.classList.remove('active');
    } else {
        group.classList.add('expanded');
        btn.classList.add('active');
    }
};

const renderFloatingActions = () => {
    const container = document.createElement('div');
    container.className = 'floating-actions';
    
    const isBasics = window.location.pathname.includes('/basics/');
    const cartLink = isBasics ? '../cart.html' : 'cart.html';
    const loginLink = isBasics ? '../login.html' : 'login.html';

    container.innerHTML = `
        <a href="${cartLink}" class="fab" title="Cart">
            🛒
            <span class="fab-badge" id="cart-count" style="display:none">0</span>
        </a>
        <a href="${loginLink}" class="fab" title="Profile">
            👤
        </a>
    `;
    document.body.appendChild(container);
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    renderSidebar();
    renderFloatingActions();
    cart.updateUI();

    // Init Theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
});
