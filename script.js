// ===============================
// Velora Cart System
// ===============================

// Get cart from localStorage
let cart = JSON.parse(localStorage.getItem("cart")) || [];


// ===============================
// Products
// ===============================

const products = [
    {
        id: 1,
        name: "Velora Classic",
        price: 499
    },
    {
        id: 2,
        name: "Velora Elegant",
        price: 599
    },
    {
        id: 3,
        name: "Velora Premium",
        price: 699
    }
];


// ===============================
// Save Cart
// ===============================

function saveCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
}


// ===============================
// Add Product To Cart
// ===============================

function addToCart(productId) {

    const product = products.find(item => item.id === productId);

    if (!product) return;

    const existingProduct = cart.find(item => item.id === productId);

    if (existingProduct) {

        existingProduct.quantity++;

    } else {

        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1
        });

    }

    saveCart();
    renderCart();
    updateCartCount();

}


// ===============================
// Render Cart
// ===============================

function renderCart() {

    const cartItems = document.querySelector(".cart-items");
    const cartTotal = document.querySelector(".cart-total");

    if (!cartItems) return;

    cartItems.innerHTML = "";

    if (cart.length === 0) {

        cartItems.innerHTML = `
            <p class="empty-cart">
                السلة فارغة
            </p>
        `;

        if (cartTotal) {
            cartTotal.textContent = "0";
        }

        updateCartCount();

        return;
    }


    let total = 0;


    cart.forEach(item => {

        const itemTotal = item.price * item.quantity;

        total += itemTotal;


        const cartItem = document.createElement("div");

        cartItem.classList.add("cart-item");


        cartItem.innerHTML = `

            <div class="cart-item-info">

                <h3>${item.name}</h3>

                <p>
                    ${item.price} EGP
                </p>

            </div>


            <div class="cart-quantity">

                <button
                    class="quantity-btn minus"
                    data-id="${item.id}">
                    −
                </button>

                <span class="quantity">
                    ${item.quantity}
                </span>

                <button
                    class="quantity-btn plus"
                    data-id="${item.id}">
                    +
                </button>

            </div>


            <div class="cart-item-total">

                ${itemTotal} EGP

            </div>


            <button
                class="remove-product"
                data-id="${item.id}">

                <i class="fa-solid fa-trash"></i>

            </button>

        `;


        cartItems.appendChild(cartItem);

    });


    if (cartTotal) {
        cartTotal.textContent = `${total} EGP`;
    }


    updateCartCount();

}


// ===============================
// Increase Quantity
// ===============================

function increaseQuantity(productId) {

    const product = cart.find(item => item.id === productId);

    if (!product) return;

    product.quantity++;

    saveCart();
    renderCart();

}


// ===============================
// Decrease Quantity
// ===============================

function decreaseQuantity(productId) {

    const product = cart.find(item => item.id === productId);

    if (!product) return;


    if (product.quantity > 1) {

        product.quantity--;

    } else {

        cart = cart.filter(item => item.id !== productId);

    }


    saveCart();
    renderCart();

}


// ===============================
// Remove Product
// ===============================

function removeFromCart(productId) {

    cart = cart.filter(item => item.id !== productId);

    saveCart();
    renderCart();

}


// ===============================
// Cart Count
// ===============================

function updateCartCount() {

    const cartCount = document.querySelector(".cart-count");

    if (!cartCount) return;


    const totalQuantity = cart.reduce(
        (total, item) => total + item.quantity,
        0
    );


    cartCount.textContent = totalQuantity;


    if (totalQuantity === 0) {

        cartCount.style.display = "none";

    } else {

        cartCount.style.display = "flex";

    }

}


// ===============================
// Buttons + / - / Delete
// ===============================

document.addEventListener("click", function (event) {


    // Plus
    if (event.target.closest(".plus")) {

        const button = event.target.closest(".plus");

        const productId = Number(button.dataset.id);

        increaseQuantity(productId);

    }


    // Minus
    if (event.target.closest(".minus")) {

        const button = event.target.closest(".minus");

        const productId = Number(button.dataset.id);

        decreaseQuantity(productId);

    }


    // Delete
    if (event.target.closest(".remove-product")) {

        const button = event.target.closest(".remove-product");

        const productId = Number(button.dataset.id);

        removeFromCart(productId);

    }

});


// ===============================
// Start
// ===============================

renderCart();
updateCartCount();