// ==========================
// VELORA ADMIN
// ==========================

console.log("Velora Admin Dashboard Loaded");


// ==========================
// LOAD ORDERS
// ==========================

function getOrders() {

    return JSON.parse(
        localStorage.getItem("orders")
    ) || [];

}


// ==========================
// LOAD PRODUCTS
// ==========================

function getProducts() {

    return JSON.parse(
        localStorage.getItem("products")
    ) || [];

}


// ==========================
// DASHBOARD DATA
// ==========================

function updateDashboard() {

    const orders = getOrders();
    const products = getProducts();

    console.log("Orders:", orders);
    console.log("Products:", products);

}


updateDashboard();