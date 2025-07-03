/**
 * Main JavaScript file for Tech Store
 */

// Define addToCart function globally
function addToCart(productId, quantity = 1, buttonEl = null) {
    if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
        window.location.href = 'login.php';
        return;
    }
    fetch('includes/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.login) {
                window.location.href = 'login.php';
                return;
            }
            if (data.success) {
                // Update cart count
                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.cartCount || '0';
                });
                animateAddToCart(buttonEl);
                showToast("🛒 Đã thêm vào giỏ hàng!", "success");
            } else {
                showToast((data.message || "Thêm sản phẩm thất bại"), "error");
            }
        })
        .catch(() => {
            showToast("Lỗi kết nối đến server!", "error");
        });
}

document.addEventListener('DOMContentLoaded', function () {
    // General "Add to Cart" functionality for product listings
    const addToCartButtons = document.querySelectorAll('.add-to-cart');

    if (addToCartButtons.length > 0) {
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.dataset.productId;
                let quantity = 1; // Default quantity
                const productItem = this.closest('.product-item');
                if (productItem) {
                    const quantityInput = productItem.querySelector('#product-quantity');
                    if (quantityInput) {
                        const inputQuantity = parseInt(quantityInput.value);
                        if (!isNaN(inputQuantity) && inputQuantity > 0) {
                            quantity = inputQuantity;
                        }
                    }
                }

                addToCart(productId, quantity, this);
            });
        });
    }

    // Price range filter
    const minPriceInput = document.querySelector('#min-price');
    const maxPriceInput = document.querySelector('#max-price');
    const filterForm = document.querySelector('#filter-form');

    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            applyFilters();
        });
    }

    // Product search
    const searchForm = document.querySelector('#search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const searchQuery = this.querySelector('input[type="search"]').value;
            window.location.href = `search.php?q=${encodeURIComponent(searchQuery)}`;
        });
    }

    // Product image gallery
    const mainImage = document.querySelector('#main-product-image');
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    if (mainImage && thumbnails.length) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function () {
                mainImage.src = this.src;
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
});

// Update cart count in header
function updateCartCount(count) {
    const cartBadge = document.querySelector('.cart-count');
    if (cartBadge) {
        cartBadge.textContent = count;
    }
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Apply filters function
function applyFilters() {
    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);

    // Get current URL and update query parameters
    const url = new URL(window.location.href);
    for (const [key, value] of params) {
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
    }

    window.location.href = url.toString();
}

// Social login buttons
document.querySelectorAll('.social-login-btn').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault();
        const provider = this.dataset.provider;
        window.location.href = `auth/${provider}_login.php`;
    });
});

// Quantity input controls
document.querySelectorAll('.quantity-control').forEach(control => {
    const input = control.querySelector('input');
    const decrementBtn = control.querySelector('.decrement');
    const incrementBtn = control.querySelector('.increment');

    if (input && decrementBtn && incrementBtn) {
        decrementBtn.addEventListener('click', () => {
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateCartItem(input);
            }
        });

        incrementBtn.addEventListener('click', () => {
            const currentValue = parseInt(input.value);
            input.value = currentValue + 1;
            updateCartItem(input);
        });

        input.addEventListener('change', () => {
            if (parseInt(input.value) < 1) {
                input.value = 1;
            }
            updateCartItem(input);
        });
    }
});

// Update cart item quantity
function updateCartItem(input) {
    const productId = input.dataset.productId;
    const quantity = input.value;

    fetch('includes/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartTotal(data.total);
                showNotification('Cart updated successfully!', 'success');
            } else {
                showNotification('Failed to update cart.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
}

// Update cart total
function updateCartTotal(total) {
    const totalElement = document.querySelector('.cart-total');
    if (totalElement) {
        totalElement.textContent = total;
    }
}
function animateAddToCart(buttonEl) {
    // Normalize the button element in case a NodeList or jQuery object is passed

    if (!buttonEl) return;

    if (buttonEl instanceof NodeList || Array.isArray(buttonEl)) {
        buttonEl = buttonEl[0];
    } else if (buttonEl.jquery) {
        buttonEl = buttonEl[0];
    }
    if (!(buttonEl instanceof Element) || typeof buttonEl.closest !== 'function') {
        return;
    }

    const productImg = buttonEl.closest('.card')?.querySelector('img');
    const cartIcon = document.querySelector('.fa-shopping-cart, .cart-icon');
    if (!productImg || !cartIcon) return;

    const imgClone = productImg.cloneNode(true);
    const imgRect = productImg.getBoundingClientRect();

    imgClone.style.position = 'fixed';
    imgClone.style.top = imgRect.top + 'px';
    imgClone.style.left = imgRect.left + 'px';
    imgClone.style.width = imgRect.width + 'px';
    imgClone.style.transition = 'transform 0.7s ease-in-out, opacity 0.7s ease-in-out';
    imgClone.style.zIndex = '1000';

    document.body.appendChild(imgClone);

    const cartRect = cartIcon.getBoundingClientRect();
    const translateX = cartRect.left - imgRect.left;
    const translateY = cartRect.top - imgRect.top;

    requestAnimationFrame(() => {
        imgClone.style.transform = `translate(${translateX}px, ${translateY}px) scale(0.1)`;
        imgClone.style.opacity = '0';
    });

    imgClone.addEventListener('transitionend', () => {
        imgClone.remove();
    });
}
function showToast(message, type = 'success') {
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `${icons[type] || ''} ${message}`;
    toast.style.display = "flex";
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 3000);
}
