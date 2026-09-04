
require("dotenv").config();

const express = require("express");
const mysql = require("mysql2");
const path = require("path");

const app = express();

const PORT = process.env.PORT || 3000;


// ========================================
// MIDDLEWARE
// ========================================

app.use(express.json());
app.use(express.urlencoded({ extended: true }));


// ========================================
// STATIC WEBSITE
// ========================================

app.use(express.static(path.join(__dirname)));


// ========================================
// MYSQL DATABASE
// ========================================

const db = mysql.createConnection({

    host: process.env.DB_HOST || "127.0.0.1",

    user: process.env.DB_USER || "root",

    password: process.env.DB_PASSWORD || "",

    database: process.env.DB_NAME || "velora",

    port: Number(process.env.DB_PORT) || 3306

});


db.connect((err) => {

    if (err) {

        console.error("========================================");
        console.error("❌ MySQL Connection Error");
        console.error("========================================");
        console.error(err.message);

        return;
    }

    console.log("========================================");
    console.log("VELORA DATABASE");
    console.log("========================================");
    console.log("MySQL Connected Successfully ✅");
    console.log("Database:", process.env.DB_NAME || "velora");
    console.log("========================================");

});


// ========================================
// TEST API
// ========================================

app.get("/api", (req, res) => {

    res.json({

        success: true,

        message: "Velora API is working 🚀"

    });

});


// ========================================
// GET PRODUCTS
// ========================================

app.get("/api/products", (req, res) => {

    const sql = `

        SELECT

            id,

            name,

            price,

            sale_price AS salePrice,

            stock,

            category,

            image,

            featured,

            created_at

        FROM products

        ORDER BY id DESC

    `;


    db.query(sql, (err, results) => {

        if (err) {

            console.error("GET PRODUCTS ERROR:");

            console.error(err.message);

            return res.status(500).json({

                success: false,

                error: "Failed to fetch products"

            });

        }


        res.json({

            success: true,

            products: results

        });

    });

});


// ========================================
// GET SINGLE PRODUCT
// ========================================

app.get("/api/products/:id", (req, res) => {

    const id = req.params.id;


    const sql = `

        SELECT

            id,

            name,

            price,

            sale_price AS salePrice,

            stock,

            category,

            image,

            featured,

            created_at

        FROM products

        WHERE id = ?

    `;


    db.query(sql, [id], (err, results) => {

        if (err) {

            console.error("GET PRODUCT ERROR:");

            console.error(err.message);

            return res.status(500).json({

                success: false,

                error: "Failed to fetch product"

            });

        }


        if (results.length === 0) {

            return res.status(404).json({

                success: false,

                error: "Product not found"

            });

        }


        res.json({

            success: true,

            product: results[0]

        });

    });

});


// ========================================
// ADD PRODUCT
// ========================================

app.post("/api/products", (req, res) => {

    const {

        name,

        price,

        salePrice,

        stock,

        category,

        image,

        featured

    } = req.body;


    if (!name || price === undefined) {

        return res.status(400).json({

            success: false,

            error: "Product name and price are required"

        });

    }


    const sql = `

        INSERT INTO products

        (

            name,

            price,

            sale_price,

            stock,

            category,

            image,

            featured

        )

        VALUES (?, ?, ?, ?, ?, ?, ?)

    `;


    const values = [

        name.trim(),

        Number(price),

        salePrice === null ||
        salePrice === ""

            ? null

            : Number(salePrice),

        Number(stock) || 0,

        category || "",

        image || "",

        featured ? 1 : 0

    ];


    db.query(sql, values, (err, result) => {

        if (err) {

            console.error("ADD PRODUCT ERROR:");

            console.error(err.message);

            return res.status(500).json({

                success: false,

                error: "Failed to add product"

            });

        }


        res.status(201).json({

            success: true,

            message: "Product added successfully",

            productId: result.insertId

        });

    });

});


// ========================================
// UPDATE PRODUCT
// ========================================

app.put("/api/products/:id", (req, res) => {

    const id = req.params.id;


    const {

        name,

        price,

        salePrice,

        stock,

        category,

        image,

        featured

    } = req.body;


    if (!name || price === undefined) {

        return res.status(400).json({

            success: false,

            error: "Product name and price are required"

        });

    }


    const sql = `

        UPDATE products

        SET

            name = ?,

            price = ?,

            sale_price = ?,

            stock = ?,

            category = ?,

            image = ?,

            featured = ?

        WHERE id = ?

    `;


    const values = [

        name.trim(),

        Number(price),

        salePrice === null ||
        salePrice === ""

            ? null

            : Number(salePrice),

        Number(stock) || 0,

        category || "",

        image || "",

        featured ? 1 : 0,

        id

    ];


    db.query(sql, values, (err, result) => {

        if (err) {

            console.error("UPDATE PRODUCT ERROR:");

            console.error(err.message);

            return res.status(500).json({

                success: false,

                error: "Failed to update product"

            });

        }


        if (result.affectedRows === 0) {

            return res.status(404).json({

                success: false,

                error: "Product not found"

            });

        }


        res.json({

            success: true,

            message: "Product updated successfully"

        });

    });

});


// ========================================
// DELETE PRODUCT
// ========================================

app.delete("/api/products/:id", (req, res) => {

    const id = req.params.id;


    const sql = `

        DELETE FROM products

        WHERE id = ?

    `;


    db.query(sql, [id], (err, result) => {

        if (err) {

            console.error("DELETE PRODUCT ERROR:");

            console.error(err.message);

            return res.status(500).json({

                success: false,

                error: "Failed to delete product"

            });

        }


        if (result.affectedRows === 0) {

            return res.status(404).json({

                success: false,

                error: "Product not found"

            });

        }


        res.json({

            success: true,

            message: "Product deleted successfully"

        });

    });

});


// ========================================
// 404 API
// ========================================

app.use("/api", (req, res) => {

    res.status(404).json({

        success: false,

        error: "API endpoint not found"

    });

});


// ========================================
// START SERVER
// ========================================

app.listen(PORT, () => {

    console.log("========================================");

    console.log("VELORA SERVER");

    console.log("========================================");

    console.log(
        `Server running on http://localhost:${PORT}`
    );

    console.log(
        `API running on http://localhost:${PORT}/api`
    );

    console.log("========================================");

});