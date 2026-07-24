<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="mb-5">
        <style>
            .dashboard-create-dropdown .btn {
                min-width: 8.5rem;
                border-radius: .75rem;
                padding: .9rem 1.15rem;
                font-weight: 600;
                box-shadow: 0 .85rem 2rem rgba(49, 46, 129, .12);
            }

            .dashboard-create-dropdown .dropdown-menu {
                width: 24rem;
                max-width: calc(100vw - 2rem);
                padding: 0;
                border: 0;
                border-radius: 1rem;
                overflow: hidden;
                box-shadow: 0 1.5rem 3rem rgba(17, 24, 39, .18);
            }

            .dashboard-create-dropdown-header {
                padding: 1.25rem 1.5rem .75rem 1.5rem;
                font-size: 2rem;
                font-weight: 500;
                color: var(--gray-900);
            }

            .dashboard-create-dropdown-section + .dashboard-create-dropdown-section {
                border-top: 1px solid var(--gray-200);
            }

            .dashboard-create-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem 1.5rem;
                color: inherit;
                text-decoration: none;
                transition: background-color .2s ease;
            }

            .dashboard-create-item:hover {
                background: var(--gray-100);
                color: inherit;
                text-decoration: none;
            }

            .dashboard-create-item-icon {
                width: 3rem;
                height: 3rem;
                flex-shrink: 0;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #f3f0ec;
                color: var(--gray-800);
                font-size: 1.1rem;
            }

            .dashboard-create-item-content {
                min-width: 0;
            }

            .dashboard-create-item-title {
                display: flex;
                align-items: center;
                gap: .5rem;
                font-size: 1.15rem;
                font-weight: 600;
                color: var(--gray-900);
                line-height: 1.2;
            }

            .dashboard-create-item-text {
                margin-top: .2rem;
                color: var(--gray-600);
                line-height: 1.35;
            }

        </style>

        <div class="d-flex justify-content-end mb-4">
            <div class="dropdown dashboard-create-dropdown">
                <button class="btn btn-success dropdown-toggle d-inline-flex align-items-center" type="button" id="dashboard_create_dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-plus mr-2"></i> <?= l('global.create') ?>
                </button>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dashboard_create_dropdown">
                    <div class="dashboard-create-dropdown-header"><?= l('global.create') ?></div>

                    <div class="dashboard-create-dropdown-section">
                        <?php if(settings()->codes->qr_codes_is_enabled): ?>
                            <a href="<?= url('qr-code-create') ?>" class="dashboard-create-item">
                                <span class="dashboard-create-item-icon"><i class="fas fa-fw fa-qrcode"></i></span>
                                <span class="dashboard-create-item-content">
                                    <span class="dashboard-create-item-title"><?= l('qr_codes.create') ?></span>
                                    <span class="d-block dashboard-create-item-text">Generate a new QR code.</span>
                                </span>
                            </a>
                        <?php endif ?>

                        <a href="https://app.seamlessqrcode.com/sso/switch?to=flipbook&redirect=dashboard" class="dashboard-create-item">
                            <span class="dashboard-create-item-icon"><i class="fas fa-fw fa-book-open"></i></span>
                            <span class="dashboard-create-item-content">
                                <span class="dashboard-create-item-title">Flipbook</span>
                                <span class="d-block dashboard-create-item-text">Create and manage interactive flipbooks.</span>
                            </span>
                        </a>

                        <?php if(settings()->links->biolinks_is_enabled): ?>
                            <a href="#create_biolink" class="dashboard-create-item" data-toggle="modal" data-target="#create_biolink">
                                <span class="dashboard-create-item-icon"><i class="fas fa-fw fa-hashtag"></i></span>
                                <span class="dashboard-create-item-content">
                                    <span class="dashboard-create-item-title"><?= l('biolink_modal.header') ?></span>
                                    <span class="d-block dashboard-create-item-text">Build a custom bio page for your profile and links.</span>
                                </span>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->biolinks_is_enabled && settings()->links->biolinks_templates_is_enabled): ?>
                            <a href="<?= url('biolinks-templates') ?>" class="dashboard-create-item">
                                <span class="dashboard-create-item-icon"><i class="fas fa-fw fa-layer-group"></i></span>
                                <span class="dashboard-create-item-content">
                                    <span class="dashboard-create-item-title"><?= l('dashboard.create_from_templates') ?></span>
                                    <span class="d-block dashboard-create-item-text"><?= l('biolinks_templates.subheader') ?></span>
                                </span>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->shortener_is_enabled): ?>
                            <a href="#create_link" class="dashboard-create-item" data-toggle="modal" data-target="#create_link">
                                <span class="dashboard-create-item-icon"><i class="fas fa-fw fa-link"></i></span>
                                <span class="dashboard-create-item-content">
                                    <span class="dashboard-create-item-title"><?= l('create_link_modal.header') ?></span>
                                    <span class="d-block dashboard-create-item-text">Create a branded short link.</span>
                                </span>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->vcards_is_enabled): ?>
                            <a href="#create_vcard" class="dashboard-create-item" data-toggle="modal" data-target="#create_vcard">
                                <span class="dashboard-create-item-icon"><i class="fas fa-fw fa-id-card"></i></span>
                                <span class="dashboard-create-item-content">
                                    <span class="dashboard-create-item-title"><?= l('create_vcard_modal.header') ?></span>
                                    <span class="d-block dashboard-create-item-text">Share a digital business card.</span>
                                </span>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->events_is_enabled): ?>
                            <a href="#create_event" class="dashboard-create-item" data-toggle="modal" data-target="#create_event">
                                <span class="dashboard-create-item-icon"><i class="fas fa-fw fa-calendar"></i></span>
                                <span class="dashboard-create-item-content">
                                    <span class="dashboard-create-item-title"><?= l('create_event_modal.header') ?></span>
                                    <span class="d-block dashboard-create-item-text">Create an event link visitors can save.</span>
                                </span>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->files_is_enabled): ?>
                            <a href="#create_file" class="dashboard-create-item" data-toggle="modal" data-target="#create_file">
                                <span class="dashboard-create-item-icon"><i class="fas fa-fw fa-file"></i></span>
                                <span class="dashboard-create-item-content">
                                    <span class="dashboard-create-item-title"><?= l('create_file_modal.header') ?></span>
                                    <span class="d-block dashboard-create-item-text">Upload a file and turn it into a shareable link.</span>
                                </span>
                            </a>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-n3 justify-content-between">
            <!-- START of new code block -->
            <?php if(settings()->codes->qr_codes_is_enabled): ?>
                <div class="col-12 col-sm-6 col-xl-4 p-3">
                    <div class="card h-100 position-relative">
                        <div class="card-body d-flex">
                            <div>
                                <div class="card border-0 mr-3 position-static" style="background: #fff7ed;">
                                    <div class="p-3 d-flex align-items-center justify-content-between">
                                        <a href="<?= url('qr-codes') ?>" class="stretched-link" style="color: #fb923c;">
                                            <i class="fas fa-fw fa-qrcode fa-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="card-title h4 m-0"><?= nr($data->qr_codes_total) ?></div>
                                <span class="text-muted"><?= l('qr_codes.title') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <!-- END of new code block -->

            <div class="col-12 col-sm-6 col-xl-4 p-3">
                <div class="card h-100 position-relative" data-flipbook-count-card>
                    <div class="card-body d-flex">
                        <div>
                            <div class="card border-0 mr-3 position-static" style="background: #eef2ff;">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <a href="https://app.seamlessqrcode.com/sso/switch?to=flipbook&redirect=dashboard" class="stretched-link" style="color: #4f46e5;" aria-label="Open flipbook dashboard">
                                        <i class="fas fa-fw fa-book-open fa-lg"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="card-title h4 m-0" data-flipbook-count>
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                            </div>
                            <span class="text-muted">Flipbooks</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if(settings()->links->biolinks_is_enabled): ?>
                <div class="col-12 col-sm-6 col-xl-4 p-3">
                    <div class="card h-100 position-relative">
                        <div class="card-body d-flex">
                            <div>
                                <div class="card border-0 mr-3 position-static" style="background: #eff6ff;">
                                    <div class="p-3 d-flex align-items-center justify-content-between">
                                        <a href="<?= url('links?type=biolink') ?>" class="stretched-link" style="color: #3b82f6;">
                                            <i class="fas fa-fw fa-hashtag fa-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="card-title h4 m-0"><?= nr($data->biolink_links_total) ?></div>
                                <span class="text-muted"><?= l('dashboard.biolinks') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            
            <?php if(settings()->links->shortener_is_enabled): ?>
                <div class="col-12 col-sm-6 col-xl-4 p-3">
                    <div class="card h-100 position-relative">
                        <div class="card-body d-flex">
                            <div>
                                <div class="card border-0 mr-3 position-static" style="background: #f0fdfa;">
                                    <div class="p-3 d-flex align-items-center justify-content-between">
                                        <a href="<?= url('links?type=link') ?>" class="stretched-link" style="color: #14b8a6;">
                                            <i class="fas fa-fw fa-link fa-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="card-title h4 m-0"><?= nr($data->link_links_total) ?></div>
                                <span class="text-muted"><?= l('dashboard.links') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->links->files_is_enabled): ?>
                <div class="col-12 col-sm-6 col-xl-4 p-3">
                    <div class="card h-100 position-relative">
                        <div class="card-body d-flex">
                            <div>
                                <div class="card border-0 mr-3 position-static" style="background: #ecfdf5;">
                                    <div class="p-3 d-flex align-items-center justify-content-between">
                                        <a href="<?= url('links?type=file') ?>" class="stretched-link" style="color: #10b981;">
                                            <i class="fas fa-fw fa-file fa-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="card-title h4 m-0"><?= nr($data->file_links_total) ?></div>
                                <span class="text-muted"><?= l('dashboard.file_links') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->links->vcards_is_enabled): ?>
                <div class="col-12 col-sm-6 col-xl-4 p-3">
                    <div class="card h-100 position-relative">
                        <div class="card-body d-flex">
                            <div>
                                <div class="card border-0 mr-3 position-static" style="background: #ecfeff;">
                                    <div class="p-3 d-flex align-items-center justify-content-between">
                                        <a href="<?= url('links?type=vcard') ?>" class="stretched-link" style="color: #06b6d4;">
                                            <i class="fas fa-fw fa-id-card fa-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="card-title h4 m-0"><?= nr($data->vcard_links_total) ?></div>
                                <span class="text-muted"><?= l('dashboard.vcard_links') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->links->events_is_enabled): ?>
                <div class="col-12 col-sm-6 col-xl-4 p-3">
                    <div class="card h-100 position-relative">
                        <div class="card-body d-flex">
                            <div>
                                <div class="card border-0 mr-3 position-static" style="background: #eef2ff;">
                                    <div class="p-3 d-flex align-items-center justify-content-between">
                                        <a href="<?= url('links?type=event') ?>" class="stretched-link" style="color: #6366f1;">
                                            <i class="fas fa-fw fa-calendar fa-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="card-title h4 m-0"><?= nr($data->event_links_total) ?></div>
                                <span class="text-muted"><?= l('dashboard.event_links') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <!--<?php if(settings()->links->static_is_enabled): ?>-->
            <!--    <div class="col-12 col-sm-6 col-xl-4 p-3">-->
            <!--        <div class="card h-100 position-relative">-->
            <!--            <div class="card-body d-flex">-->
            <!--                <div>-->
            <!--                    <div class="card border-0 mr-3 position-static" style="background: #fdf4ff;">-->
            <!--                        <div class="p-3 d-flex align-items-center justify-content-between">-->
            <!--                            <a href="<?= url('links?type=static') ?>" class="stretched-link" style="color: #c026d3;">-->
            <!--                                <i class="fas fa-fw fa-file-code fa-lg"></i>-->
            <!--                            </a>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->

            <!--                <div>-->
            <!--                    <div class="card-title h4 m-0"><?= nr($data->static_links_total) ?></div>-->
            <!--                    <span class="text-muted"><?= l('dashboard.static_links') ?></span>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--<?php endif ?>-->
        </div>

        <div class="card border-0 shadow-sm mt-2 mb-5">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-7 mb-4 mb-lg-0">
                        <div class="embed-responsive embed-responsive-16by9 rounded overflow-hidden">
                            <iframe
                                class="embed-responsive-item"
                                src="https://www.youtube.com/embed/JwyjVW5Hu5Y?si=Ggdj98Y1q_Ld-JL3"
                                title="How to start creating using Seamless Qr Code"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="text-uppercase text-primary small font-weight-bold mb-2">Getting Started</div>
                        <h2 class="h3 mb-3">How to start creating using Seamless Qr Code</h2>
                        <p class="text-muted mb-3">Watch this quick walkthrough to learn how to create your first QR code, bio page, short link, flipbook, and more inside Seamless Qr Code.</p>
                        <p class="text-muted mb-0">This guide is a good starting point if you want to understand the dashboard, the create options, and how to begin publishing assets faster.</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if($data->links_chart): ?>
            <div class="card mt-5">
                <div class="card-body">
                    <div class="chart-container <?= !$data->links_chart['is_empty'] ? null : 'd-none' ?>">
                        <canvas id="pageviews_chart"></canvas>
                    </div>
                    <?= !$data->links_chart['is_empty'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>

                    <?php if(!$data->links_chart['is_empty'] && settings()->main->chart_cache ?? 12): ?>
                        <small class="text-muted"><i class="fas fa-fw fa-sm fa-info-circle mr-1"></i> <?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?></small>
                    <?php endif ?>
                </div>
            </div>

<?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>

            <?php ob_start() ?>
            <script>
                if(document.getElementById('pageviews_chart')) {
                    let css = window.getComputedStyle(document.body);
                    let pageviews_color = css.getPropertyValue('--primary');
                    let visitors_color = css.getPropertyValue('--gray-300');
                    let pageviews_color_gradient = null;
                    let visitors_color_gradient = null;

                    /* Chart */
                    let pageviews_chart = document.getElementById('pageviews_chart').getContext('2d');

                    /* Colors */
                    pageviews_color_gradient = pageviews_chart.createLinearGradient(0, 0, 0, 250);
                    pageviews_color_gradient.addColorStop(0, set_hex_opacity(pageviews_color, 0.6));
                    pageviews_color_gradient.addColorStop(1, set_hex_opacity(pageviews_color, 0.1));

                    visitors_color_gradient = pageviews_chart.createLinearGradient(0, 0, 0, 250);
                    visitors_color_gradient.addColorStop(0, set_hex_opacity(visitors_color, 0.6));
                    visitors_color_gradient.addColorStop(1, set_hex_opacity(visitors_color, 0.1));

                    new Chart(pageviews_chart, {
                        type: 'line',
                        data: {
                            labels: <?= $data->links_chart['labels'] ?? '[]' ?>,
                            datasets: [
                                {
                                    label: <?= json_encode(l('link.statistics.pageviews')) ?>,
                                    data: <?= $data->links_chart['pageviews'] ?? '[]' ?>,
                                    backgroundColor: pageviews_color_gradient,
                                    borderColor: pageviews_color,
                                    fill: true
                                },
                                {
                                    label: <?= json_encode(l('link.statistics.visitors')) ?>,
                                    data: <?= $data->links_chart['visitors'] ?? '[]' ?>,
                                    backgroundColor: visitors_color_gradient,
                                    borderColor: visitors_color,
                                    fill: true
                                }
                            ]
                        },
                        options: chart_options
                    });
                }
            </script>
            <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
        <?php endif ?>

        <?php ob_start() ?>
        <script>
            'use strict';

            (() => {
                const card = document.querySelector('[data-flipbook-count-card]');

                if(!card) {
                    return;
                }

                const endpoint = <?= json_encode(url('dashboard/get_flipbook_stats_ajax')) ?>;
                const countElement = card.querySelector('[data-flipbook-count]');

                const formatNumber = value => {
                    try {
                        return new Intl.NumberFormat().format(Number(value) || 0);
                    } catch (error) {
                        return String(Number(value) || 0);
                    }
                };

                const renderLoading = () => {
                    countElement.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status"></span>';
                };

                const renderCount = value => {
                    countElement.textContent = '0';
                    countElement.textContent = formatNumber(value);
                };

                const loadFlipbookStats = async () => {
                    renderLoading();

                    try {
                        const response = await fetch(endpoint, {
                            method: 'get',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        let data = null;

                        try {
                            data = await response.json();
                        } catch (error) {
                            data = null;
                        }

                        if(!response.ok || !data || data.status !== 'success' || !data.details) {
                            throw new Error('Unable to load flipbook statistics.');
                        }

                        renderCount(data.details.flipbooks || 0);
                    } catch (error) {
                        renderCount(0);
                    }
                };

                loadFlipbookStats();
                window.setInterval(loadFlipbookStats, 60000);
            })();
        </script>
        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
    </div>

    <?= $this->views['links_content'] ?>
</div>
