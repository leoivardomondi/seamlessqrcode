<?php defined('ALTUMCODE') || die() ?>
<!DOCTYPE html>
<html lang="<?= \Altum\Language::$code ?>" dir="<?= l('direction') ?>" class="w-100 h-100">
<head>
    <title><?= \Altum\Title::get() ?></title>
    <base href="<?= SITE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <?php require THEME_PATH . 'views/partials/pwa_head.php' ?>

    <link rel="alternate" href="<?= SITE_URL . \Altum\Router::$original_request ?>" hreflang="x-default" />
    <?php if(count(\Altum\Language::$active_languages) > 1): ?>
        <?php foreach(\Altum\Language::$active_languages as $language_name => $language_code): ?>
            <?php if(settings()->main->default_language != $language_name): ?>
                <link rel="alternate" href="<?= SITE_URL . $language_code . '/' . \Altum\Router::$original_request ?>" hreflang="<?= $language_code ?>" />
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>

    <?php if(!empty(settings()->main->favicon)): ?>
        <link href="<?= settings()->main->favicon_full_url ?>" rel="icon" />
    <?php endif ?>

    <?php $admin_cache_buster = PRODUCT_CODE . '_' . (file_exists(ASSETS_PATH . 'css/admin-custom.css') ? filemtime(ASSETS_PATH . 'css/admin-custom.css') : '1'); ?>
    <link href="<?= ASSETS_FULL_URL . 'css/admin-' . \Altum\ThemeStyle::get_file() . '?v=' . $admin_cache_buster ?>" id="css_theme_style" rel="stylesheet" media="screen,print">
    <?php foreach(['admin-custom.css', 'libraries/select2.css'] as $file): ?>
        <link href="<?= ASSETS_FULL_URL ?>css/<?= $file ?>?v=<?= $admin_cache_buster ?>" rel="stylesheet" media="screen,print">
    <?php endforeach ?>

    <style>
        .admin-sidebar {
            transition: min-width .2s ease, max-width .2s ease, width .2s ease, margin .15s linear !important;
        }
        .admin-content {
            transition: margin-left .2s ease, margin-right .2s ease !important;
        }

        .admin-sidebar-collapse-btn {
            cursor: pointer !important;
            z-index: 105 !important;
            position: relative;
            border: 1px solid var(--gray-200);
            border-radius: .35rem;
            background: var(--white);
            color: var(--gray-700) !important;
            width: 32px;
            height: 32px;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            transition: all .15s ease;
        }

        .admin-sidebar-collapse-btn:hover {
            background: var(--gray-100);
            color: var(--gray-900) !important;
            border-color: var(--gray-300);
        }

        .admin-sidebar-collapse-btn i,
        .admin-sidebar-collapse-btn svg {
            pointer-events: none !important;
        }

        @media (min-width: 768px) {
            body.admin-sidebar-collapsed .admin-sidebar {
                min-width: 75px !important;
                max-width: 75px !important;
                width: 75px !important;
            }

            body.admin-sidebar-collapsed .admin-content {
                margin-left: calc(75px + 1.5rem) !important;
            }

            [dir="rtl"] body.admin-sidebar-collapsed .admin-content {
                margin-left: initial !important;
                margin-right: calc(75px + 1.5rem) !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-title {
                justify-content: center !important;
                padding: 0 0.25rem !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-title #sidebar_title {
                display: none !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-collapse-btn {
                margin-left: 0 !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-links-wrapper {
                padding: 1rem 0.35rem !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-links li > a {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0.65rem 0 !important;
                font-size: 0 !important;
                text-align: center !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-links li > a > i,
            body.admin-sidebar-collapsed .admin-sidebar-links li > a > svg {
                margin-right: 0 !important;
                font-size: 1.15rem !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-links li > a .badge {
                position: absolute !important;
                top: 2px !important;
                right: 4px !important;
                font-size: 0.65rem !important;
                padding: 0.15rem 0.35rem !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-links > .divider-wrapper {
                padding: 0 !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-links > .divider-wrapper > .divider {
                margin: 0.5rem 0 !important;
            }

            body.admin-sidebar-collapsed .admin-sidebar-links li.dropdown .dropdown-menu {
                left: 80px !important;
                top: 0 !important;
            }
        }
    </style>

    <?= \Altum\Event::get_content('head') ?>

        <?php if(is_logged_in() && !user()->plan_settings->export->pdf): ?>
            <style>@media print { body { display: none; } }</style>
        <?php endif ?>
</head>

<body class="<?= l('direction') == 'rtl' ? 'rtl' : null ?>" data-theme-style="<?= \Altum\ThemeStyle::get() ?>">
<script>
    if(localStorage.getItem('admin_sidebar_collapsed') === 'true') {
        document.body.classList.add('admin-sidebar-collapsed');
    }
</script>
<div id="admin_overlay" class="admin-overlay" style="display: none"></div>
<?php if(settings()->main->admin_spotlight_is_enabled || settings()->main->user_spotlight_is_enabled) require THEME_PATH . 'views/partials/spotlight.php' ?>
<?php require THEME_PATH . 'views/partials/pwa_install.php' ?>

<div class="admin-container">
    <?= $this->views['admin_sidebar'] ?>

    <section class="admin-content altum-animate altum-animate-fill-none altum-animate-fade-in">
        <?= $this->views['admin_menu'] ?>

        <div class="p-3 p-lg-5 position-relative">
            <?= include_view(THEME_PATH . 'views/admin/partials/admin_version_updates_bar.php') ?>
            <?= include_view(THEME_PATH . 'views/admin/partials/admin_support_bar.php') ?>

            <?= $this->views['content'] ?>

            <div class="card mt-4 d-print-none">
                <div class="card-body">
                    <?= $this->views['footer'] ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?= \Altum\Event::get_content('modals') ?>

<?php require THEME_PATH . 'views/partials/js_global_variables.php' ?>

<?php foreach(['libraries/jquery.slim.min.js', 'libraries/popper.min.js', 'libraries/bootstrap.min.js', 'custom.js'] as $file): ?>
    <script src="<?= ASSETS_FULL_URL ?>js/<?= $file ?>?v=<?= PRODUCT_CODE ?>"></script>
<?php endforeach ?>

<?php foreach(['libraries/select2.min.js', 'admin_custom.js', 'libraries/fontawesome.min.js', 'libraries/fontawesome-solid.min.js', 'libraries/fontawesome-brands.min.js',] as $file): ?>
    <script src="<?= ASSETS_FULL_URL ?>js/<?= $file ?>?v=<?= $admin_cache_buster ?>" defer></script>
<?php endforeach ?>

<?= \Altum\Event::get_content('javascript') ?>

</body>
</html>
