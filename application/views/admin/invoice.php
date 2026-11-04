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
    <link rel="preload" href=<?php echo base_url('assets/adminlte/css/adminlte.min.css'); ?> as="style" />
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
    <link rel="stylesheet" href=<?php echo base_url('assets/adminlte/css/adminlte.min.css'); ?> />
    <!--end::Required Plugin(AdminLTE)-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
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

                    <!--begin::User Menu Dropdown-->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img src=<?php echo base_url('assets/adminlte/img/user2-160x160.jpg'); ?> class="user-image rounded-circle shadow"
                                alt="Alexander Pierce" />
                            <span class="d-none d-xl-inline">Alexander Pierce</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <!--begin::User Image-->
                            <li class="user-header text-bg-primary">
                                <img src=<?php echo base_url('assets/adminlte/img/user2-160x160.jpg'); ?> class="rounded-circle shadow" alt="Alexander Pierce" />
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
                                <?php echo form_open('auth/logout'); ?>
                                <button submit="POST" class="btn btn-outline-danger float-end">Sign out</button>
                                <?php echo form_close(); ?>
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
                    <img src=<?php echo base_url('assets/adminlte/img/AdminLTELogo.png'); ?> alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
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
                            <a href=<?php echo site_url('admin/dashboard'); ?> class="nav-link">
                                <i class="nav-icon bi bi bi-speedometer2"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?php echo site_url('admin/users'); ?> class="nav-link">
                                <i class="nav-icon bi bi bi-people"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?php echo site_url('admin/products'); ?> class="nav-link">
                                <i class="nav-icon bi bi bi-box-seam"></i>
                                <p>Products</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?php echo site_url('admin/invoices'); ?> class="nav-link">
                                <i class="nav-icon bi bi bi-bag-check"></i>
                                <p>Invoices</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?php echo site_url('admin/receipts'); ?> class="nav-link">
                                <i class="nav-icon bi bi bi-file-earmark-text"></i>
                                <p>Receipts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?php echo site_url('admin/payments'); ?> class="nav-link">
                                <i class="nav-icon bi bi bi-receipt"></i>
                                <p>Payment Logs</p>
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
                            <h1 class="mb-0 fs-3">Invoice Details</h1>
                            <div class="col-12">
                                <p class="text-muted mb-0">
                                    Invoice <?= html_escape($invoice->invoice_number) ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb float-sm-end">
                                    <li class="breadcrumb-item"><a href=<?= site_url('admin/dashboard') ?>>Home</a>
                                    </li>
                                    <li class="breadcrumb-item"><a href=<?= site_url('admin/invoices') ?>>Invoices</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Invoice Detail</li>
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
                <!--begin::Container-->


                <div class="container-fluid py-3">
                    <!-- Invoice Card -->
                    <div class="card invoice-card">
                        <div class="card-body p-4 p-md-5">

                            <!-- Invoice Header -->
                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <h2 class="fw-bold text-primary">PayDesk</h2>
                                    <p class="text-muted mb-0">Official Invoice</p>
                                </div>

                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <h4 class="fw-bold">INVOICE</h4>
                                    <h5 class="text-primary">
                                        <?= html_escape($invoice->invoice_number) ?>
                                    </h5>
                                    <p class="mb-1">
                                        <strong>Invoice Date:</strong>
                                        <?= html_escape(date('F j, Y', strtotime($invoice->issued_at))) ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Order No.:</strong>
                                        <?= html_escape($invoice->order_number) ?>
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <!-- Customer Information -->
                            <div class="row my-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase mb-3">Bill To</h6>
                                    <h5 class="fw-bold">
                                        <?= html_escape($invoice->name) ?>
                                    </h5>
                                    <p class="mb-0">
                                        <?= html_escape($invoice->email) ?>
                                    </p>
                                </div>

                                <div class="col-md-6 text-md-end mt-4 mt-md-0">
                                    <h6 class="text-muted text-uppercase mb-3">Invoice Information</h6>
                                    <p class="mb-1">
                                        <strong>Invoice Number:</strong>
                                        <?= html_escape($invoice->invoice_number) ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Order Number:</strong>
                                        <?= html_escape($invoice->order_number) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="table-responsive mt-5">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Line Total</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <?= html_escape($item->product_name) ?>
                                            </td>
                                            <td class="text-center">
                                                <?= (int) $item->quantity ?>
                                            </td>
                                            <td class="text-end">
                                                <?= html_escape($invoice->currency) ?>
                                                <?= number_format((float) $item->unit_price, 2) ?>
                                            </td>
                                            <td class="text-end">
                                                <?= html_escape($invoice->currency) ?>
                                                <?= number_format((float) $item->line_total, 2) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals -->
                            <div class="row justify-content-end mt-4">
                                <div class="col-md-5 col-lg-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal</span>
                                        <strong>
                                            <?= html_escape($invoice->currency) ?>
                                            <?= number_format((float) $invoice->subtotal, 2) ?>
                                        </strong>
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between fs-5">
                                        <strong>Total</strong>
                                        <strong class="text-primary">
                                            <?= html_escape($invoice->currency) ?>
                                            <?= number_format((float) $invoice->total, 2) ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="text-center mt-5 pt-4 border-top">
                                <h6 class="fw-bold">Thank you for your purchase!</h6>
                                <p class="text-muted small mb-0">
                                    This invoice was generated electronically by PayDesk.
                                </p>
                            </div>

                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="d-flex justify-content-between gap-2 mt-4 receipt-actions">
                        <a href="<?= site_url('admin/invoices') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
                        </a>

                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Invoice
                        </button>
                    </div>
                </div>

                <style>
                    @media print {
                        body {
                            background: #fff !important;
                        }

                        .app-sidebar,
                        .app-header,
                        .app-footer,
                        .app-main>.app-content-header,
                        .no-print,
                        button,
                        a.btn {
                            display: none !important;
                        }

                        .app-wrapper,
                        .app-main,
                        .app-content {
                            margin: 0 !important;
                            padding: 0 !important;
                        }

                        .invoice-card {
                            border: none !important;
                            box-shadow: none !important;
                        }

                        .card-body {
                            padding: 0 !important;
                        }
                    }
                </style>


                <!--end::Container-->
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
    <script src=<?php echo base_url('assets/adminlte/js/adminlte.min.js'); ?>></script>
    <!--end::Required Plugin(AdminLTE)-->
</body>
<!--end::Body-->

</html>
