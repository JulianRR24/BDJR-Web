// assets/js/admin.js
import { apiRequest } from './api.js';
import { isAuthenticated } from './auth.js';

if (!isAuthenticated()) {
    window.location.href = 'login.html';
}

const tableBody = document.getElementById('products-table-body');
const modal = document.getElementById('product-modal');
const form = document.getElementById('product-form');
const addBtn = document.getElementById('add-product-btn');
const closeBtns = document.querySelectorAll('.close-btn, .close-modal-btn');

let products = [];

// Fetch and Render
async function loadProducts() {
    try {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Cargando...</td></tr>';
        const data = await apiRequest('products.php'); // Reuse public endpoint for reading
        products = data;
        renderTable();
    } catch (error) {
        console.error(error);
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar productos</td></tr>';
    }
}

function renderTable() {
    tableBody.innerHTML = products.map(p => `
        <tr>
            <td><img src="${p.image_url}" alt="${p.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;"></td>
            <td class="font-bold">${p.name}</td>
            <td>$${new Intl.NumberFormat('es-CO').format(p.price)}</td>
            <td>${p.stock || 0}</td>
            <td>
                <button class="btn btn-sm btn-outline btn-edit" data-id="${p.id}">✏️</button>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${p.id}">🗑️</button>
            </td>
        </tr>
    `).join('');
}

// Modal Logic
function openModal(product = null) {
    const title = document.getElementById('modal-title');
    const idInput = document.getElementById('p-id');
    
    // Reset Form
    form.reset();
    
    if (product) {
        title.textContent = 'Editar Producto';
        idInput.value = product.id;
        document.getElementById('p-name').value = product.name;
        document.getElementById('p-vendor').value = product.vendor || 'BDJR';
        document.getElementById('p-category').value = product.category || 'General';
        document.getElementById('p-price').value = product.price;
        document.getElementById('p-compare').value = product.compare_price || '';
        document.getElementById('p-desc').value = product.description || '';
        document.getElementById('p-long-desc').value = product.long_description || '';
        
        // Arrays to Text
        const features = typeof product.features === 'string' ? JSON.parse(product.features) : (product.features || []);
        const benefits = typeof product.benefits === 'string' ? JSON.parse(product.benefits) : (product.benefits || []);
        
        document.getElementById('p-features').value = features.join('\n');
        document.getElementById('p-benefits').value = benefits.join('\n');
    } else {
        title.textContent = 'Nuevo Producto';
        idInput.value = '';
    }
    
    modal.style.display = 'block';
}

function closeModal() {
    modal.style.display = 'none';
}

addBtn.onclick = () => openModal();
closeBtns.forEach(btn => btn.onclick = closeModal);
window.onclick = (e) => { if (e.target == modal) closeModal(); };

// Edit & Delete Actions
tableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-edit')) {
        const id = e.target.dataset.id;
        const product = products.find(p => p.id == id);
        openModal(product);
    }
    
    if (e.target.classList.contains('btn-delete')) {
        if (confirm('¿Seguro que deseas eliminar este producto?')) {
            const id = e.target.dataset.id;
            try {
                await apiRequest(`product_manage.php?id=${id}`, 'DELETE');
                loadProducts();
            } catch (error) {
                alert('Error al eliminar: ' + error.message);
            }
        }
    }
});

// Form Submit
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const saveBtn = document.getElementById('save-btn');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Guardando...';
    saveBtn.disabled = true;

    try {
        const id = document.getElementById('p-id').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `product_manage.php?id=${id}` : 'product_manage.php';

        const featuresRaw = document.getElementById('p-features').value;
        const benefitsRaw = document.getElementById('p-benefits').value;

        const data = {
            name: document.getElementById('p-name').value,
            vendor: document.getElementById('p-vendor').value,
            category: document.getElementById('p-category').value,
            price: document.getElementById('p-price').value,
            compare_price: document.getElementById('p-compare').value,
            description: document.getElementById('p-desc').value,
            long_description: document.getElementById('p-long-desc').value,
            features: featuresRaw.split('\n').filter(line => line.trim() !== ''),
            benefits: benefitsRaw.split('\n').filter(line => line.trim() !== ''),
            stock: 100 // Default stock for now
        };

        await apiRequest(url, method, data);
        closeModal();
        loadProducts();
        alert(id ? 'Producto actualizado' : 'Producto creado');

    } catch (error) {
        alert('Error: ' + error.message);
        console.error(error);
    } finally {
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    }
});

// Init
loadProducts();
