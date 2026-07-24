<?php defined('ALTUMCODE') || die() ?>

<!-- ===============================
     HERO SECTION (FULL WIDTH)
================================ -->
<div class="w-100 bg-gradient-to-r from-primary-50 to-primary-100 py-5">
    <div class="container-fluid px-4 text-center">
        <div class="row align-items-center justify-content-center">
            <div class="col-12 col-lg-6 mb-4 mb-lg-0 text-lg-left">
                <h1 class="display-5 font-weight-bold mb-3">
                    Explore Our <span class="text-primary">Digital Menu</span>
                </h1>
                <p class="lead text-muted mb-4">
                    Scan, browse, and order your favorite dishes and drinks — whether dining in or from home.
                </p>
                <div class="d-flex flex-column flex-md-row justify-content-center justify-content-lg-start">
                    <a href="#menu" class="btn btn-primary mb-2 mb-md-0 mr-md-3 rounded-pill px-4 py-2">
                        View Menu <i class="fas fa-utensils ml-2"></i>
                    </a>
                    <a href="https://wa.me/2547XXXXXXXX?text=I'd%20like%20to%20place%20an%20order"
                       target="_blank"
                       class="btn btn-outline-success rounded-pill px-4 py-2">
                        Order via WhatsApp <i class="fab fa-whatsapp ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="col-10 col-lg-5 text-center">
                <img src="<?= get_custom_image_if_any('restaurant/hero-menu.webp') ?>"
                     alt="Digital Restaurant Menu"
                     class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

<!-- ===============================
     MENU SECTION
================================ -->
<div id="menu" class="container-fluid px-4 py-5 bg-white">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Browse Our Menu</h2>
        <p class="text-muted">
            Select a category below to view flipping or scrolling PDF menus.
        </p>
    </div>

    <div class="row justify-content-center">
        <!-- Food -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body text-center">
                    <i class="fas fa-hamburger fa-3x text-primary mb-3"></i>
                    <h5 class="fw-bold">Food</h5>
                    <p class="text-muted small">View our full selection of dishes.</p>
                    <a href="/uploads/menus/Food.pdf" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                        View PDF Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Drinks -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body text-center">
                    <i class="fas fa-coffee fa-3x text-primary mb-3"></i>
                    <h5 class="fw-bold">Drinks</h5>
                    <p class="text-muted small">Cocktails, juices, and more.</p>
                    <a href="/uploads/menus/Drinks.pdf" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                        View PDF Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Pizza -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body text-center">
                    <i class="fas fa-pizza-slice fa-3x text-primary mb-3"></i>
                    <h5 class="fw-bold">Pizza</h5>
                    <p class="text-muted small">Freshly baked, loaded with flavor.</p>
                    <a href="/uploads/menus/Pizza.pdf" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                        View PDF Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Specials -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-3x text-primary mb-3"></i>
                    <h5 class="fw-bold">Special Offers</h5>
                    <p class="text-muted small">Exclusive combos and discounts.</p>
                    <a href="/uploads/menus/Specials.pdf" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                        View PDF Menu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===============================
     ORDERING SECTION
================================ -->
<div class="w-100 bg-light py-5">
    <div class="container-fluid px-4 text-center">
        <h2 class="fw-bold mb-4">Order for Delivery or Takeaway</h2>
        <p class="text-muted mb-5">
            You can order directly with the waiter while dining in — or make your order online for home delivery.
        </p>
        <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
            <a href="https://wa.me/2547XXXXXXXX?text=I'd%20like%20to%20order%20for%20delivery"
               target="_blank"
               class="btn btn-success rounded-pill px-4 py-2 mb-3 mb-md-0">
                Order via WhatsApp <i class="fab fa-whatsapp ml-2"></i>
            </a>
            <a href="<?= url('restaurant/order') ?>"
               class="btn btn-primary rounded-pill px-4 py-2">
                Order Online <i class="fas fa-shopping-bag ml-2"></i>
            </a>
        </div>
    </div>
</div>

<!-- ===============================
     SEO & SCHEMA
================================ -->
<?php ob_start() ?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Restaurant",
    "name": "Seamless QR Restaurant Menu",
    "image": "<?= get_custom_image_if_any('restaurant/hero-menu.webp') ?>",
    "servesCuisine": "Multi-cuisine",
    "priceRange": "$$",
    "url": "<?= SITE_URL ?>restaurant",
    "telephone": "+2547XXXXXXXX",
    "sameAs": [
        "https://www.facebook.com/yourpage",
        "https://www.instagram.com/yourpage"
    ]
}
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<!-- ===============================
     STYLE OVERRIDES
================================ -->
<style>
html, body {
    overflow-x: hidden;
}
.btn {
    transition: all 0.3s ease;
}
.btn:hover {
    transform: translateY(-2px);
}
.card {
    border-radius: 1rem;
}
.card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
.bg-gradient-to-r {
    background: linear-gradient(90deg, #f9fafb, #eef2ff);
}
</style>
