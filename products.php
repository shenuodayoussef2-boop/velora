<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <title>Products | Velora Admin</title>

    <link rel="stylesheet"
        href="admin.css">

    <style>

        .products-page {
            max-width: 1200px;
            margin: auto;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .products-header h1 {
            font-size: 30px;
        }

        .add-product-btn {
            border: none;
            background: #111;
            color: white;
            padding: 13px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .add-product-btn i {
            margin-left: 7px;
        }

        .product-form {
            display: none;
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,.05);
        }

        .product-form.show {
            display: block;
        }

        .product-form h2 {
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 13px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 8px;
            outline: none;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #111;
        }

        .form-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .save-btn,
        .cancel-btn {
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            cursor: pointer;
        }

        .save-btn {
            background: #111;
            color: white;
        }

        .cancel-btn {
            background: #eee;
        }

        .products-table {
            background: white;
            border-radius: 15px;
            padding: 20px;
            overflow-x: auto;
        }

        .products-table table {
            min-width: 800px;
        }

        .product-name {
            font-weight: bold;
        }

        .delete-btn {
            border: none;
            background: #ffe1e1;
            color: #d00;
            padding: 8px 12px;
            border-radius: 7px;
            cursor: pointer;
        }

        .empty-products {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .products-header {
                align-items: flex-start;
                gap: 15px;
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="admin-logo">

            <h2>Velora</h2>

            <span>ADMIN PANEL</span>

        </div>


        <ul class="sidebar-menu">

            <li>
                <a href="admin.html">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="active">
                <a href="products.html">
                    <i class="fa-solid fa-box"></i>
                    <span>Products</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Categories</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Orders</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Inventory</span>
                </a>
            </li>

        </ul>


        <div class="sidebar-bottom">

            <a href="index.html">

                <i class="fa-solid fa-store"></i>

                View Store

            </a>

        </div>

    </aside>


    <!-- MAIN -->

    <main class="admin-main">

        <div class="products-page">


            <!-- HEADER -->

            <div class="products-header">

                <div>

                    <p class="header-subtitle">
                        VELORA STORE
                    </p>

                    <h1>
                        المنتجات
                    </h1>

                </div>


                <button
                    class="add-product-btn"
                    id="open-form">

                    <i class="fa-solid fa-plus"></i>

                    إضافة منتج

                </button>

            </div>


            <!-- FORM -->

            <div
                class="product-form"
                id="product-form">

                <h2>
                    إضافة منتج جديد
                </h2>


                <form id="add-product-form">

                    <div class="form-grid">


                        <div class="form-group">

                            <label>
                                اسم المنتج
                            </label>

                            <input
                                type="text"
                                id="product-name"
                                placeholder="مثال: Classic Shirt"
                                required>

                        </div>


                        <div class="form-group">

                            <label>
                                السعر
                            </label>

                            <input
                                type="number"
                                id="product-price"
                                placeholder="49"
                                min="0"
                                required>

                        </div>


                        <div class="form-group">

                            <label>
                                الكمية في المخزون
                            </label>

                            <input
                                type="number"
                                id="product-stock"
                                placeholder="50"
                                min="0"
                                required>

                        </div>


                        <div class="form-group">

                            <label>
                                Category
                            </label>

                            <select id="product-category">

                                <option value="Fashion">
                                    Fashion
                                </option>

                                <option value="Accessories">
                                    Accessories
                                </option>

                                <option value="Shoes">
                                    Shoes
                                </option>

                                <option value="Perfumes">
                                    Perfumes
                                </option>

                                <option value="Beauty">
                                    Beauty
                                </option>

                                <option value="Lifestyle">
                                    Lifestyle
                                </option>

                            </select>

                        </div>


                        <div class="form-group full">

                            <label>
                                رابط صورة المنتج
                            </label>

                            <input
                                type="url"
                                id="product-image"
                                placeholder="https://..."
                                required>

                        </div>


                        <div class="form-group full">

                            <label>
                                وصف المنتج
                            </label>

                            <textarea
                                id="product-description"
                                rows="4"
                                placeholder="اكتب وصف المنتج..."></textarea>

                        </div>

                    </div>


                    <div class="form-buttons">

                        <button
                            type="submit"
                            class="save-btn">

                            <i class="fa-solid fa-check"></i>

                            حفظ المنتج

                        </button>


                        <button
                            type="button"
                            class="cancel-btn"
                            id="cancel-form">

                            إلغاء

                        </button>

                    </div>

                </form>

            </div>


            <!-- PRODUCTS -->

            <div class="products-table">

                <table>

                    <thead>

                        <tr>

                            <th>المنتج</th>

                            <th>السعر</th>

                            <th>Category</th>

                            <th>المخزون</th>

                            <th>الإجراء</th>

                        </tr>

                    </thead>


                    <tbody id="products-list">
                    </tbody>

                </table>

            </div>


        </div>

    </main>


<script>

const openForm =
    document.querySelector("#open-form");

const cancelForm =
    document.querySelector("#cancel-form");

const productForm =
    document.querySelector("#product-form");

const addProductForm =
    document.querySelector("#add-product-form");

const productsList =
    document.querySelector("#products-list");


// ==========================
// OPEN FORM
// ==========================

openForm.addEventListener("click", function() {

    productForm.classList.add("show");

});


// ==========================
// CLOSE FORM
// ==========================

cancelForm.addEventListener("click", function() {

    productForm.classList.remove("show");

    addProductForm.reset();

});


// ==========================
// LOAD PRODUCTS
// ==========================

function getProducts() {

    return JSON.parse(
        localStorage.getItem("products")
    ) || [];

}


// ==========================
// SAVE PRODUCTS
// ==========================

function saveProducts(products) {

    localStorage.setItem(
        "products",
        JSON.stringify(products)
    );

}


// ==========================
// RENDER PRODUCTS
// ==========================

function renderProducts() {

    const products = getProducts();

    productsList.innerHTML = "";


    if (products.length === 0) {

        productsList.innerHTML = `

            <tr>

                <td
                    colspan="5"
                    class="empty-products">

                    لا توجد منتجات حتى الآن

                </td>

            </tr>

        `;

        return;

    }


    products.forEach(function(product, index) {

        productsList.innerHTML += `

            <tr>

                <td>

                    <div class="product-name">

                        ${product.name}

                    </div>

                </td>

                <td>
                    ${product.price} EGP
                </td>

                <td>
                    ${product.category}
                </td>

                <td>
                    ${product.stock}
                </td>

                <td>

                    <button
                        class="delete-btn"
                        onclick="deleteProduct(${index})">

                        <i class="fa-solid fa-trash"></i>

                        حذف

                    </button>

                </td>

            </tr>

        `;

    });

}


// ==========================
// ADD PRODUCT
// ==========================

addProductForm.addEventListener(
    "submit",
    function(e) {

        e.preventDefault();


        const product = {

            id: Date.now(),

            name:
                document
                    .querySelector("#product-name")
                    .value
                    .trim(),

            price:
                Number(
                    document
                        .querySelector("#product-price")
                        .value
                ),

            stock:
                Number(
                    document
                        .querySelector("#product-stock")
                        .value
                ),

            category:
                document
                    .querySelector("#product-category")
                    .value,

            image:
                document
                    .querySelector("#product-image")
                    .value
                    .trim(),

            description:
                document
                    .querySelector("#product-description")
                    .value
                    .trim()

        };


        const products =
            getProducts();


        products.push(product);


        saveProducts(products);


        renderProducts();


        addProductForm.reset();


        productForm.classList.remove("show");


        alert(
            "تمت إضافة المنتج بنجاح 🎉"
        );

    }
);


// ==========================
// DELETE PRODUCT
// ==========================

function deleteProduct(index) {

    const products =
        getProducts();


    if (
        !confirm(
            "هل أنت متأكد من حذف المنتج؟"
        )
    ) {
        return;
    }


    products.splice(index, 1);


    saveProducts(products);


    renderProducts();

}


// ==========================
// START
// ==========================

renderProducts();

</script>

</body>

