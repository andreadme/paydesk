<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PayDesk | Dashboard</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
        (() => {
            'use strict';
            const root = document.documentElement;

            // Applications with their own theming opt out of AdminLTE's color mode
            // entirely, here as well as in the bundle.
            if (root.getAttribute('data-lte-color-mode') === 'off') {
                return;
            }

            const STORAGE_KEY = 'lte-theme';
            let stored = null;
            try {
                stored = localStorage.getItem(STORAGE_KEY);
            } catch {
                // localStorage may be unavailable (private mode, sandboxed iframe).
            }
            // Mirror the precedence in color-mode.ts: the visitor's stored choice
            // wins, then a theme this page declared itself, then the OS preference.
            const authored = root.getAttribute('data-bs-theme');
            let resolved = 'light';
            if (stored === 'dark' || stored === 'light') {
                resolved = stored;
            } else if (authored === 'dark' || authored === 'light') {
                resolved = authored;
            } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
                resolved = 'dark';
            }
            root.setAttribute('data-bs-theme', resolved);
            root.style.colorScheme = resolved;
            // Flag values computed here, so the bundle does not mistake them for a
            // theme the page declared and stop following the OS preference.
            if (resolved !== authored) {
                root.setAttribute('data-lte-theme-resolved', '');
            }
        })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="PayDesk | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta name="description"
        content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind." />
    <meta name="keywords"
        content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel" />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href=<?= base_url('assets/adminlte/css/adminlte.min.css') ?> as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.3.0/index.css"
        integrity="sha256-5UwXtC6TaPHCAbVQ59waEAqOssHyhn/twyuLRRWszzo=" crossorigin="anonymous" media="print"
        onload="this.media = 'all'" />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href=<?= base_url('assets/adminlte/css/adminlte.min.css') ?> />
    <!--end::Required Plugin(AdminLTE)-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <?php
        $cart_items = $this->session->userdata('cart') ?: [];
        $total_quantity = array_sum(array_column($cart_items, 'quantity'));
        $subtotal = array_sum(array_column($cart_items, 'line_total'));
        ?>
        <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"
                            aria-label="Toggle sidebar">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                </ul>
                <!--end::Start Navbar Links-->

                <!--begin::End Navbar Links-->
                <ul class="navbar-nav ms-auto">
                    <!--begin::Search (small screens: the field above is hidden, so link to the search page)-->
                    <li class="nav-item d-xl-none">
                        <a class="nav-link" href="./pages/search-results.html" aria-label="Search">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </a>
                    </li>
                    <!--end::Search-->


                    <li class="nav-item">
                        <button class="btn btn-link nav-link position-relative" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#shoppingCart" aria-controls="shoppingCart"
                            aria-label="Open shopping cart">
                            <i class="bi bi-cart3 fs-5"></i>

                            <span class="position-absolute badge rounded-pill bg-danger" style="top: 2px; right: 0;">
                                <?= $total_quantity ?? 0 ?>
                            </span>
                        </button>
                    </li>


                    <!--begin::User Menu Dropdown-->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img src=<?= base_url('assets/adminlte/img/user2-160x160.jpg') ?>
                                class="user-image rounded-circle shadow" alt="Alexander Pierce" />
                            <span class="d-none d-xl-inline">Alexander Pierce</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <!--begin::User Image-->
                            <li class="user-header text-bg-primary">
                                <img src=<?= base_url('assets/adminlte/img/user2-160x160.jpg') ?>
                                    class="rounded-circle shadow" alt="Alexander Pierce" />
                                <p>
                                    Alexander Pierce - Web Developer
                                    <small>Member since Nov. 2023</small>
                                </p>
                            </li>
                            <!--end::User Image-->
                            <!--begin::Menu Body-->
                            <li class="user-body">
                                <!--begin::Row-->
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <a href="#">Followers</a>
                                    </div>
                                    <div class="col-4 text-center">
                                        <a href="#">Sales</a>
                                    </div>
                                    <div class="col-4 text-center">
                                        <a href="#">Friends</a>
                                    </div>
                                </div>
                                <!--end::Row-->
                            </li>
                            <!--end::Menu Body-->
                            <!--begin::Menu Footer-->
                            <li class="user-footer">
                                <?= form_open('auth/logout') ?>
                                <button submit="POST" class="btn btn-outline-danger float-end">Sign out</button>
                                <?= form_close() ?>
                            </li>
                            <!--end::Menu Footer-->
                        </ul>
                    </li>
                    <!--end::User Menu Dropdown-->
                </ul>
                <!--end::End Navbar Links-->
            </div>
            <!--end::Container-->
        </nav>
        <!--end::Header-->
        <!--begin::Sidebar-->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <!--begin::Sidebar Brand-->
            <div class="sidebar-brand">
                <!--begin::Brand Link-->
                <a href="./index.html" class="brand-link">
                    <!--begin::Brand Image-->
                    <img src=<?= base_url('assets/adminlte/img/AdminLTELogo.png') ?> alt="AdminLTE Logo"
                        class="brand-image opacity-75 shadow" />
                    <!--end::Brand Image-->
                    <!--begin::Brand Text-->
                    <span class="brand-text fw-light">PayDesk</span>
                    <!--end::Brand Text-->
                </a>
                <!--end::Brand Link-->
            </div>
            <!--end::Sidebar Brand-->
            <!--begin::Sidebar Search-->
            <div class="sidebar-search" role="search">
                <label for="sidebar-search-input" class="visually-hidden">Filter menu</label>
                <input type="search" id="sidebar-search-input" class="form-control form-control-sm"
                    placeholder="Filter menu…" autocomplete="off" data-lte-toggle="sidebar-search"
                    data-lte-target="#navigation" />
                <p class="fs-7 text-secondary mt-2 mb-0" data-lte-search-empty role="status" hidden>
                    No matching pages.
                </p>
            </div>
            <!--end::Sidebar Search-->
            <!--begin::Sidebar Wrapper-->
            <div class="sidebar-wrapper">
                <nav class="mt-2" aria-label="Main navigation">
                    <!--begin::Sidebar Menu-->
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false"
                        id="navigation">

                        <li class="nav-item">
                            <a href=<?= site_url('customer/dashboard') ?> class="nav-link">
                                <i class="nav-icon bi-speedometer2"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?= site_url('customer/products') ?> class="nav-link">
                                <i class="nav-icon bi bi-box-seam"></i>
                                <p>Products</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?= site_url('customer/dashboard') ?> class="nav-link">
                                <i class="nav-icon bi bi-bag-check"></i>
                                <p>My Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?= site_url('customer/my_invoices') ?> class="nav-link">
                                <i class="nav-icon bi bi-file-earmark-text"></i>
                                <p>My Invoices</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?= site_url('customer/my_receipts') ?> class="nav-link">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>My Receipts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="./generate/theme.html" class="nav-link">
                                <i class="nav-icon bi bi-person"></i>
                                <p>My Profile</p>
                            </a>
                        </li>
                    </ul>
                    <!--end::Sidebar Menu-->
                </nav>
            </div>
            <!--end::Sidebar Wrapper-->
        </aside>
        <!--end::Sidebar-->
        <!--begin::App Main-->
        <main class="app-main">
            <!--begin::App Content Header-->
            <div class="app-content-header">
                <!--begin::Container-->
                <div class="container-fluid">
                    <!--begin::Row-->
                    <div class="row">
                        <div class="col-sm-6">
                            <h1 class="mb-0 fs-3">My Invoices</h1>
                            <div class="col-12">
                                <p class="text-muted">View and manage your purchase invoices.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb float-sm-end">
                                    <li class="breadcrumb-item"><a href=<?php echo site_url('customer/dashboard'); ?>>Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">My Invoices</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::App Content Header-->
            <!--begin::App Content-->

            <div class="app-content">

                <div class="container-fluid">
                    <div class="row g-4">
                        <!-- Invoice List -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">Invoice History</h5>
                            </div>

                            <div class="card-body p-0">

                                <?php if (empty($invoices)): ?>

                                <!-- Empty State -->
                                <div class="text-center py-5 px-3">
                                    <div class="mb-3">
                                        <i class="bi bi-receipt fs-1 text-secondary"></i>
                                    </div>

                                    <h5 class="fw-semibold">No invoices yet</h5>

                                    <p class="text-muted mb-0">
                                        Your purchase invoices will appear here.
                                    </p>
                                </div>

                                <?php else: ?>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-3">Invoice Number</th>
                                                <th>Order Number</th>
                                                <th>Issue Date</th>
                                                <th>Total</th>
                                                <th class="text-end px-3">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td class="px-3 fw-semibold">
                                                    <?= html_escape($invoice->invoice_number) ?>
                                                </td>

                                                <td>
                                                    <?= html_escape($invoice->order_number) ?>
                                                </td>

                                                <td>
                                                    <?= html_escape($invoice->issued_at) ?>
                                                </td>

                                                <td class="fw-semibold">
                                                    <?= html_escape(number_format((float) $invoice->total, 2)) ?>
                                                    <?= html_escape($invoice->currency) ?>
                                                </td>

                                                <td class="text-end px-3">
                                                    <a href="<?= site_url('customer/my_invoice/' . $invoice->id) ?>"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye me-1"></i>
                                                        View Invoice
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <!--end::App Content-->
        </main>
        <!--end::App Main-->
        <!--begin::Footer-->
        <footer class="app-footer">
            <!--begin::To the end-->
            <div class="float-end d-none d-sm-inline"></div>
            <!--end::To the end-->
            <!--begin::Copyright-->
            <strong>
                Copyright &copy; 2014-2026&nbsp;
                <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
            </strong>
            All rights reserved.
            <!--end::Copyright-->
        </footer>
        <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous">
    </script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src=<?= base_url('assets/adminlte/js/adminlte.min.js') ?>></script>
    <!--end::Required Plugin(AdminLTE)-->


    <div class="offcanvas offcanvas-end" tabindex="-1" id="shoppingCart" aria-labelledby="shoppingCartLabel"
        style="width: 420px; max-width: 100%;">
        <!-- Header -->
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="shoppingCartLabel">
                <i class="bi bi-cart3 me-2"></i>
                Shopping Cart
            </h5>

            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <!-- Cart Body -->
        <div class="offcanvas-body d-flex flex-column">

            <!-- Cart Items -->
            <div class="flex-grow-1">
                <?php if (empty($cart_items)): ?>

                <div class="text-center py-5">
                    <i class="bi bi-cart-x fs-1 text-muted"></i>
                    <h5 class="mt-3">Your cart is empty</h5>
                    <p class="text-muted">Looks like you haven't added anything yet.</p>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="offcanvas">
                        Continue Shopping
                    </button>
                </div>

                <?php else: ?>
                <?php foreach ($cart_items as $product_id => $cart_item): ?>
                <!-- Cart Item 1 -->
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-light rounded p-3">
                        <i class="bi bi-mouse fs-2 text-secondary"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?= html_escape($cart_item['name']) ?></h6>
                        <small class="text-muted">$<?= number_format((float) $cart_item['price'], 2) ?> each</small>

                        <div class="d-flex align-items-center gap-2 mt-2">
                            <?= form_open('customer/decrease_cart_quantity', ['class' => 'cart-update-form']) ?>
                            <input type="hidden" name="product_id" value="<?= (int) $product_id ?>">
                            <button submit="POST" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-dash"></i>
                            </button>
                            <?= form_close() ?>

                            <input type="number" name="quantity" class="form-control form-control-sm text-center"
                                style="width: 75px;" value="<?= (int) $cart_item['quantity'] ?>" min="1"
                                max="9999" step="1" required>

                            <?= form_open('customer/increase_cart_quantity', ['class' => 'cart-update-form']) ?>
                            <input type="hidden" name="product_id" value="<?= (int) $product_id ?>">
                            <button submit="POST" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-plus"></i>
                            </button>
                            <?= form_close() ?>
                        </div>
                    </div>

                    <div class="text-end">
                        <strong>$<?= number_format((float) $cart_item['line_total'], 2) ?></strong>

                        <?= form_open('customer/clear_cart_item', ['class' => 'cart-update-form']) ?>
                        <input type="hidden" name="product_id" value="<?= (int) $product_id ?>">
                        <button submit="POST" class="btn btn-sm text-danger d-block ms-auto">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?= form_close() ?>
                    </div>
                </div>

                <hr>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Cart Summary -->
            <div class="border-top pt-3 mt-auto">
                <?php $warning = $this->session->flashdata('warning'); ?>
                <?php if ($warning): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= html_escape($warning) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <?php $info = $this->session->flashdata('info'); ?>

                <?php if ($info): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <?= html_escape($info) ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <strong>$<?= $subtotal ?></strong>
                </div>

                <p class="small text-muted">
                    Shipping and other charges, if applicable,
                    will be calculated during checkout.
                </p>

                <?= form_open('customer/checkout') ?>

                <button submit="POST" class="btn btn-primary w-100">Proceed to Checkout<i
                        class="bi bi-arrow-right ms-2"></i></button>

                <?= form_close() ?>

                <button type="button" class="btn btn-outline-secondary w-100 mt-2" data-bs-dismiss="offcanvas">
                    Continue Shopping
                </button>

                <?= form_open('customer/clear_cart', ['class' => 'cart-update-form']) ?>
                <button type="submit" class="btn btn-outline-danger w-100 mt-2">
                    <i class="bi bi-trash me-1"></i>
                    Clear Cart
                </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cartElement = document.getElementById('shoppingCart');

            if (sessionStorage.getItem('reopenCart') === 'true') {
                sessionStorage.removeItem('reopenCart');

                const cart = bootstrap.Offcanvas.getOrCreateInstance(cartElement);
                cart.show();
            }

            cartElement.addEventListener('submit', function(event) {
                if (event.target.matches('.cart-update-form')) {
                    sessionStorage.setItem('reopenCart', 'true');
                }
            });
        });
    </script>

</body>
<!--end::Body-->

</html>
