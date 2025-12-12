// assets/js/cart.js

export const cart = {
    items: [],

    init() {
        const storedCart = localStorage.getItem('cart');
        if (storedCart) {
            this.items = JSON.parse(storedCart);
        }
        this.updateUI();
    },

    add(product) {
        const existingItem = this.items.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            this.items.push({ ...product, quantity: 1 });
        }
        this.save();
        this.updateUI();
        alert('Producto agregado al carrito');
    },

    remove(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.save();
        this.updateUI();
    },

    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            item.quantity = parseInt(quantity);
            if (item.quantity <= 0) {
                this.remove(productId);
            } else {
                this.save();
                this.updateUI();
            }
        }
    },

    clear() {
        this.items = [];
        this.save();
        this.updateUI();
    },

    save() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    },

    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    },

    updateUI() {
        // Update cart count badge if it exists
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = count;
        }
    }
};

cart.init();
