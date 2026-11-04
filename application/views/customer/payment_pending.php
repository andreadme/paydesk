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
        <!--begin::App Main-->
        <main class="app-main">
            <!--begin::App Content-->

            <div class="app-content">

                <div class="container-fluid">

                    <div class="container py-5">
                        <div class="row justify-content-center">
                            <div class="col-lg-7 col-md-9">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center p-4 p-md-5">

                                        <!-- Pending Icon -->
                                        <div class="mb-4">
                                            <div class="rounded-circle bg-warning-subtle d-inline-flex align-items-center justify-content-center"
                                                style="width: 90px; height: 90px;">
                                                <i class="bi bi-clock-history text-warning"
                                                    style="font-size: 3rem;"></i>
                                            </div>
                                        </div>

                                        <!-- Heading -->
                                        <h2 class="fw-bold mb-3">Payment Processing</h2>

                                        <p class="text-secondary mb-4">
                                            We're waiting for confirmation of your payment.
                                            This may take a few moments. You can check your
                                            order status from your account.
                                        </p>

                                        <!-- Purchase Summary -->
                                        <div class="bg-body-tertiary rounded p-4 text-start mb-4">
                                            <h5 class="fw-semibold mb-3">Purchase Summary</h5>

                                            <div class="d-flex justify-content-between mb-3">
                                                <span class="text-secondary">Order Number</span>
                                                <strong>
                                                    <?= html_escape($order->order_number) ?>
                                                </strong>
                                            </div>

                                            <div class="d-flex justify-content-between mb-3">
                                                <span class="text-secondary">Payment Status</span>
                                                <span class="badge text-bg-warning">Pending</span>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <span class="text-secondary">Total Amount</span>
                                                <strong>
                                                    <?= html_escape($payment->currency) ?>
                                                    <?= number_format((float) ($payment->total), 2) ?>
                                                </strong>
                                            </div>
                                        </div>

                                        <!-- Notice -->
                                        <div class="alert alert-info text-start mb-4" role="alert">
                                            <i class="bi bi-info-circle-fill me-2"></i>
                                            Please don't make another payment for this order.
                                            Your payment status will be updated once confirmation
                                            is received.
                                        </div>

                                        <!-- Actions -->
                                        <div class="d-grid gap-2">
                                            <a href="<?= site_url('customer/orders') ?>" class="btn btn-primary">
                                                <i class="bi bi-receipt me-2"></i>
                                                View My Orders
                                            </a>

                                            <a href="<?= site_url('customer/products') ?>"
                                                class="btn btn-outline-secondary">
                                                Continue Shopping
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!--end::App Content-->
        </main>
        <!--end::App Main-->
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

</body>
<!--end::Body-->

</html>
