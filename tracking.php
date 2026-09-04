<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <title>تتبع الطلب | Velora</title>

    <link rel="stylesheet"
        href="style.css">

</head>

<body>


<!-- Start Header -->

<nav class="nav-links">

    <div class="container">

        <ul>

            <li>
                <a href="index.html#home">
                    الرئيسية
                </a>
            </li>

            <li>
                <a href="index.html#products">
                    المنتجات
                </a>
            </li>

            <li>
                <a href="cart.html">
                    السلة
                </a>
            </li>

            <li>
                <a href="tracking.html">
                    تتبع الطلب
                </a>
            </li>

        </ul>


        <div class="nav-icon">

            <a href="index.html">
                Velora
            </a>

        </div>

    </div>

</nav>

<!-- End Header -->



<!-- Start Tracking -->

<section class="tracking-page">

    <div class="tracking-container">


        <div class="tracking-header">

            <p>
                VELORA ORDERS
            </p>

            <h1>
                تتبع طلبك
            </h1>

            <span>
                أدخل رقم الطلب لمعرفة حالته
            </span>

        </div>


        <!-- Search -->

        <div class="tracking-search">

            <input
                type="text"
                id="order-input"
                placeholder="مثال: VEL-123456789">

            <button id="track-btn">

                تتبع الطلب

                <i class="fa-solid fa-magnifying-glass"></i>

            </button>

        </div>


        <p
            id="tracking-error"
            class="tracking-error">
        </p>


        <!-- Result -->

        <div
            id="tracking-result"
            class="tracking-result">


            <div class="tracking-order-info">

                <div>

                    <span>
                        رقم الطلب
                    </span>

                    <strong id="tracking-order-number">
                        -
                    </strong>

                </div>


                <div>

                    <span>
                        الإجمالي
                    </span>

                    <strong id="tracking-total">
                        -
                    </strong>

                </div>

            </div>



            <!-- Timeline -->

            <div class="tracking-timeline">


                <!-- Step 1 -->

                <div
                    class="tracking-step active">

                    <div class="tracking-step-icon">

                        <i class="fa-solid fa-clipboard-check"></i>

                    </div>

                    <div>

                        <h3>
                            تم استلام الطلب
                        </h3>

                        <p>
                            تم استلام طلبك بنجاح
                        </p>

                    </div>

                </div>



                <!-- Step 2 -->

                <div class="tracking-step">

                    <div class="tracking-step-icon">

                        <i class="fa-solid fa-box"></i>

                    </div>

                    <div>

                        <h3>
                            قيد التجهيز
                        </h3>

                        <p>
                            يتم تجهيز منتجاتك
                        </p>

                    </div>

                </div>



                <!-- Step 3 -->

                <div class="tracking-step">

                    <div class="tracking-step-icon">

                        <i class="fa-solid fa-truck"></i>

                    </div>

                    <div>

                        <h3>
                            خرج للتوصيل
                        </h3>

                        <p>
                            طلبك مع شركة التوصيل
                        </p>

                    </div>

                </div>



                <!-- Step 4 -->

                <div class="tracking-step">

                    <div class="tracking-step-icon">

                        <i class="fa-solid fa-house"></i>

                    </div>

                    <div>

                        <h3>
                            تم التسليم
                        </h3>

                        <p>
                            تم تسليم طلبك بنجاح
                        </p>

                    </div>

                </div>


            </div>


        </div>

    </div>

</section>

<!-- End Tracking -->



<script>

// ==========================
// ELEMENTS
// ==========================

const orderInput =
    document.querySelector("#order-input");

const trackButton =
    document.querySelector("#track-btn");

const trackingResult =
    document.querySelector("#tracking-result");

const trackingError =
    document.querySelector("#tracking-error");

const orderNumber =
    document.querySelector(
        "#tracking-order-number"
    );

const total =
    document.querySelector(
        "#tracking-total"
    );


// ==========================
// HIDE RESULT
// ==========================

trackingResult.style.display = "none";


// ==========================
// TRACK ORDER
// ==========================

trackButton.addEventListener(
    "click",
    function() {


        const enteredOrder =
            orderInput.value
                .trim()
                .toUpperCase();


        trackingError.textContent = "";


        // Check empty
        if (!enteredOrder) {

            trackingError.textContent =
                "من فضلك أدخل رقم الطلب";

            trackingResult.style.display =
                "none";

            return;

        }


        // Get last order
        const order =
            JSON.parse(
                localStorage.getItem("lastOrder")
            );


        // Check order
        if (
            !order ||
            order.orderNumber !== enteredOrder
        ) {

            trackingError.textContent =
                "لم يتم العثور على هذا الطلب";

            trackingResult.style.display =
                "none";

            return;

        }


        // Show order
        trackingResult.style.display =
            "block";


        orderNumber.textContent =
            order.orderNumber;


        total.textContent =
            `${order.total} EGP`;


        // Start first step
        updateTrackingSteps(1);

    }
);



// ==========================
// UPDATE STEPS
// ==========================

function updateTrackingSteps(currentStep) {

    const steps =
        document.querySelectorAll(
            ".tracking-step"
        );


    steps.forEach(
        function(step, index) {

            if (index < currentStep) {

                step.classList.add("active");

            } else {

                step.classList.remove("active");

            }

        }
    );

}

</script>


</body>

</html>