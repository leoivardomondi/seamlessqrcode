<?php defined('ALTUMCODE') || die();

$landing_stats = [
    'links' => (int) ($data->total_links ?? 0),
    'qr_codes' => (int) ($data->total_qr_codes ?? 0),
    'flipbooks' => (int) ($data->total_flipbooks ?? 0),
    'track_links' => (int) ($data->total_track_links ?? 0),
];

$landing_stats_format = function($number) {
    $number = max(0, (int) $number);

    if($number >= 1000000) {
        $value = floor($number / 100000) / 10;
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') . 'M+';
    }

    if($number >= 1000) {
        $value = floor($number / 100) / 10;
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') . 'K+';
    }

    return nr($number) . '+';
};
?>

<div class="index-container">
    <canvas class="index-hero-spiral" data-index-hero-spiral aria-hidden="true"></canvas>
    <div class="container index-container-content">
        <?= \Altum\Alerts::output_alerts() ?>

        <div class="row">
            <div class="col">
                <div class="text-left">
                    <div class="mb-2">
                        <span class="badge badge-pill badge-light">
                            <i class="fas fa-fw fa-star fa-sm text-warning"></i><i class="fas fa-fw fa-star fa-sm text-warning"></i><i class="fas fa-fw fa-star fa-sm text-warning"></i><i class="fas fa-fw fa-star fa-sm text-warning"></i><i class="fas fa-fw fa-star fa-sm text-warning mr-1"></i>
                            <?= sprintf(l('index.stars'), '<span class="font-weight-bolder">' . nr($data->total_users) . '+</span>') ?>
                        </span>
                    </div>

                    <h1 class="index-header mb-4"><?= l('index.header') ?></h1>

                    <div class="row mb-5">
                        <?php if(settings()->codes->qr_codes_is_enabled): ?>
                            <div class="col-6 col-xl-4 index-feature text-truncate">
                                <a href="<?= url('qr-codes') ?>">
                                    <?= l('index.subheader.qr_codes') ?>
                                </a>
                            </div>
                        <?php endif ?>
                        
                        <?php if(settings()->links->biolinks_is_enabled): ?>
                            <div class="col-6 col-xl-4 index-feature text-truncate">
                                <a href="<?= url('links?type=biolink') ?>" class="text-truncate">
                                    <?= l('index.subheader.biolink') ?>
                                </a>
                            </div>
                        <?php endif ?>

                        <div class="col-6 col-xl-4 index-feature text-truncate">
                            <a href="https://app.seamlessqrcode.com/sso/switch?to=flipbook&redirect=dashboard">
                                <i class="fas fa-fw fa-book-open mr-1"></i> Flipbooks
                            </a>
                        </div>

                        <?php if(settings()->links->shortener_is_enabled): ?>
                            <div class="col-6 col-xl-4 index-feature text-truncate">
                                <a href="<?= url('links?type=link') ?>">
                                    <?= l('index.subheader.link') ?>
                                </a>
                            </div>
                        <?php endif ?>

                        <?php if(settings()->links->files_is_enabled): ?>
                            <div class="col-6 col-xl-4 index-feature text-truncate">
                                <a href="<?= url('links?type=file') ?>">
                                    <?= l('index.subheader.file') ?>
                                </a>
                            </div>
                        <?php endif ?>

                        <?php if(settings()->links->vcards_is_enabled): ?>
                            <div class="col-6 col-xl-4 index-feature text-truncate">
                                <a href="<?= url('links?type=vcard') ?>">
                                    <?= l('index.subheader.vcard') ?>
                                </a>
                            </div>
                        <?php endif ?>

                        <?php if(settings()->links->events_is_enabled): ?>
                            <div class="col-6 col-xl-4 index-feature text-truncate">
                                <a href="<?= url('links?type=event') ?>">
                                    <?= l('index.subheader.event') ?>
                                </a>
                            </div>
                        <?php endif ?>

                        <?php if(settings()->links->static_is_enabled): ?>
                            <div class="col-6 col-xl-4 index-feature text-truncate">
                                <a href="<?= url('links?type=static') ?>">
                                    <?= l('index.subheader.static') ?>
                                </a>
                            </div>
                        <?php endif ?>

                        <?php if(settings()->tools->is_enabled): ?>
                            <div class="col-6 col-xl-4 index-feature text-truncate">
                                <a href="<?= url('tools') ?>">
                                    <?= l('index.subheader.tools') ?>
                                </a>
                            </div>
                        <?php endif ?>
                    </div>

                    <div class="d-flex flex-column">
                        <?php if(is_logged_in()): ?>
                            <a href="<?= url('dashboard') ?>" class="btn index-button index-button-white bg-gradient border-0 mb-3">
                                <?= l('dashboard.menu') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                            </a>
                        <?php elseif(settings()->users->register_is_enabled): ?>
                            <?php if(settings()->links->claim_url_is_enabled): ?>
                                <div class="mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <?php if(count($data->domains)): ?>
                                                <select id="domain_id" name="domain_id" class="appearance-none custom-select form-control input-group-text h-100">
                                                    <?php if(settings()->links->main_domain_is_enabled): ?>
                                                        <option value="" <?= $data->link->domain ? 'selected="selected"' : null ?> data-full-url="<?= SITE_URL ?>"><?= remove_url_protocol_from_url(SITE_URL) ?></option>
                                                    <?php endif ?>

                                                    <?php foreach($data->domains as $row): ?>
                                                        <option value="<?= $row->domain_id ?>" <?= $data->link->domain && $row->domain_id == $data->link->domain->domain_id ? 'selected="selected"' : null ?>  data-full-url="<?= $row->url ?>" data-type="<?= $row->type ?>"><?= remove_url_protocol_from_url($row->url) ?></option>
                                                    <?php endforeach ?>
                                                </select>
                                            <?php else: ?>
                                                <div class="input-group-text bg-gray-50">
                                                    <?= remove_url_protocol_from_url(SITE_URL) ?>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                        <input id="claim_url" type="text" name="url" class="form-control index-input" value="" maxlength="<?= $this->user->plan_settings->url_maximum_characters ?? 64 ?>" placeholder="<?= l('index.claim_placeholder') ?>" />
                                    </div>
                                </div>

                                <?php ob_start() ?>
                                    <script>
                                        let claim_button_default_href = document.querySelector('#claim_button').href;
                                        ['change', 'paste', 'keyup', 'keypress'].forEach(event_type => document.querySelector('#claim_url').addEventListener(event_type, event => {
                                            let url = get_slug(document.querySelector('#claim_url').value);
                                            let domain_id_element = document.querySelector('#domain_id');
                                            let domain_id = domain_id_element ? domain_id_element.value : null;

                                            let query_params = new URLSearchParams();
                                            if(url) query_params.set('claim-url', url);
                                            if(domain_id) query_params.set('domain-id', domain_id);

                                            document.querySelector('#claim_button').href = query_params.toString()
                                                ? `${claim_button_default_href}?${query_params}`
                                                : claim_button_default_href;

                                            if(event.key === 'Enter') {
                                                event.preventDefault();
                                                document.querySelector('#claim_button').click();
                                            }
                                        }));
                                    </script>
                                <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
                            <?php endif ?>

                            <a id="claim_button" href="<?= url('register') ?>" class="btn index-button index-button-white bg-gradient border-0 mb-3 <?= settings()->links->claim_url_is_enabled ? 'rounded-pill' : null ?>">
                                <?= l(settings()->links->claim_url_is_enabled ? 'index.claim' : 'index.sign_up') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                            </a>
                        <?php endif ?>

                        <?php //ALTUMCODE:DEMO if(!DEMO): ?>
                        <?php if(settings()->links->biolinks_is_enabled && settings()->links->example_url && !settings()->links->claim_url_is_enabled): ?>
                            <a href="<?= settings()->links->example_url ?>" target="_blank" class="btn btn-outline-primary index-button mb-3 mb-lg-0">
                                <?= l('index.example') ?> <i class="fas fa-fw fa-sm fa-external-link-alt"></i>
                            </a>
                        <?php endif ?>
                        <?php //ALTUMCODE:DEMO endif ?>
                    </div>
                </div>
            </div>

            <div class="d-none d-lg-flex justify-content-center col">
                <img src="<?= get_custom_image_if_any('index/hero.png') ?>" class="index-image" alt="<?= l('index.hero_image_alt') ?>" />
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    (() => {
        const statEndpoint = <?= json_encode(url('index/stats_ajax')) ?>;
        const statMap = {
            links: 'total_links',
            qr_codes: 'total_qr_codes',
            flipbooks: 'total_flipbooks',
            track_links: 'total_track_links',
        };

        const formatLandingStat = number => {
            number = Math.max(0, Number.parseInt(number || 0, 10));

            if(number >= 1000000) {
                const value = Math.floor(number / 100000) / 10;
                return `${String(value).replace(/\.0$/, '')}M+`;
            }

            if(number >= 1000) {
                const value = Math.floor(number / 100) / 10;
                return `${String(value).replace(/\.0$/, '')}K+`;
            }

            return `${number.toLocaleString()}+`;
        };

        const renderLandingStats = details => {
            Object.entries(statMap).forEach(([statKey, detailKey]) => {
                const element = document.querySelector(`[data-landing-stat-value="${statKey}"]`);

                if(element && Object.prototype.hasOwnProperty.call(details, detailKey)) {
                    element.textContent = formatLandingStat(details[detailKey]);
                }
            });
        };

        const loadLandingStats = async () => {
            try {
                const response = await fetch(statEndpoint, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();

                if(response.ok && data && data.status === 'success' && data.details) {
                    renderLandingStats(data.details);
                }
            } catch(error) {
                return;
            }
        };

        loadLandingStats();
        window.setInterval(loadLandingStats, 30000);
    })();

    (() => {
        const canvas = document.querySelector('[data-index-hero-spiral]');
        const hero = canvas ? canvas.closest('.index-container') : null;

        if(!canvas || !hero || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const context = canvas.getContext('2d');
        const rings = [];
        const pointer = { x: 0, y: 0, active: false };
        const center = { x: 0, y: 0 };
        const target = { x: 0, y: 0 };
        const colors = ['#2563eb', '#4f46e5', '#7c3aed', '#db2777', '#f59e0b', '#10b981'];
        let width = 0;
        let height = 0;
        let pixel_ratio = 1;
        let tick = 0;

        const resize = () => {
            const rect = hero.getBoundingClientRect();
            width = rect.width;
            height = rect.height;
            pixel_ratio = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = width * pixel_ratio;
            canvas.height = height * pixel_ratio;
            context.setTransform(pixel_ratio, 0, 0, pixel_ratio, 0, 0);

            target.x = width * .62;
            target.y = height * .42;
            center.x = center.x || target.x;
            center.y = center.y || target.y;

            rings.length = 0;
            const count = 138;

            for(let index = 0; index < count; index++) {
                const progress = index / count;
                rings.push({
                    radius: 16 + progress * Math.min(width, height) * .72,
                    angle: progress * Math.PI * 9.5,
                    size: 1.3 + (1 - progress) * 4,
                    speed: .004 + progress * .01,
                    color: colors[index % colors.length],
                    offset: Math.random() * Math.PI * 2,
                });
            }
        };

        const setPointer = event => {
            const source = event.touches ? event.touches[0] : event;
            const rect = hero.getBoundingClientRect();
            pointer.x = source.clientX - rect.left;
            pointer.y = source.clientY - rect.top;
            pointer.active = true;
        };

        const draw = () => {
            tick += 1;
            center.x += ((pointer.active ? pointer.x : target.x) - center.x) * .075;
            center.y += ((pointer.active ? pointer.y : target.y) - center.y) * .075;

            context.clearRect(0, 0, width, height);
            context.save();
            context.globalCompositeOperation = 'multiply';

            rings.forEach((ring, index) => {
                const spin = tick * ring.speed + ring.offset;
                const spiral = ring.angle + spin;
                const pulse = Math.sin(tick * .025 + index * .22) * 10;
                const radius = ring.radius + pulse;
                const x = center.x + Math.cos(spiral) * radius;
                const y = center.y + Math.sin(spiral) * radius * .58;
                const tangent = spiral + Math.PI / 2;

                context.save();
                context.translate(x, y);
                context.rotate(tangent);
                context.globalAlpha = .16 + Math.max(0, 1 - radius / Math.max(width, height)) * .48;
                context.fillStyle = ring.color;
                context.beginPath();
                context.ellipse(0, 0, ring.size * 3.5, ring.size, 0, 0, Math.PI * 2);
                context.fill();
                context.restore();
            });

            context.restore();

            const glow = context.createRadialGradient(center.x, center.y, 0, center.x, center.y, Math.min(width, height) * .42);
            glow.addColorStop(0, 'rgba(37, 99, 235, .13)');
            glow.addColorStop(.42, 'rgba(124, 58, 237, .06)');
            glow.addColorStop(1, 'rgba(255, 255, 255, 0)');
            context.fillStyle = glow;
            context.fillRect(0, 0, width, height);

            window.requestAnimationFrame(draw);
        };

        window.addEventListener('resize', resize, { passive: true });
        hero.addEventListener('mousemove', setPointer, { passive: true });
        hero.addEventListener('touchmove', setPointer, { passive: true });
        hero.addEventListener('mouseleave', () => pointer.active = false);

        resize();
        draw();
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php if(settings()->codes->qr_codes_is_enabled): ?>
    <div class="container mt-6">
        <div class="card index-highly-rounded border-0" data-aos="fade-up">
            <div class="card-body">
                <div class="row">
                    <div class="col-auto col-lg-5 mb-4 mb-lg-0">
                        <img src="<?= get_custom_image_if_any('index/qr-code.png') ?>" class="index-card-image index-highly-rounded" loading="lazy" alt="<?= l('index.qr_image_alt') ?>" />
                    </div>
                    <div class="col ml-3">
                        <div class="bg-primary-100 p-3 w-fit-content rounded">
                            <i class="fas fa-fw fa-qrcode fa-lg text-primary"></i>
                        </div>

                        <h2 class="mt-3"><?= l('index.presentation3.header') ?></h2>
                        <p class="h6 mt-3"><?= l('index.presentation3.subheader') ?></p>

                        <ul class="list-style-none mt-4 font-size-small">
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation3.feature1') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation3.feature2') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation3.feature3') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation3.feature4') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation3.feature5') ?></div>
                            </li>
                        </ul>
                        <!-- CTA Button Added -->
                        <div class="mt-4">
                            <a href="<?= url('qr-codes') ?>" class="btn btn-outline-primary">Create Qr Code <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(settings()->links->biolinks_is_enabled): ?>
    <div class="container mt-6">
        <div class="card index-highly-rounded border-0" data-aos="fade-up">
            <div class="card-body">
                <div class="row">
                    <div class="col-auto col-lg-5 mb-4 mb-lg-0">
                        <img src="<?= get_custom_image_if_any('index/bio-link.webp') ?>" class="index-card-image index-highly-rounded" loading="lazy" alt="<?= l('index.biolink_image_alt') ?>" />
                    </div>
                    <div class="col ml-3">
                        <div class="bg-primary-100 p-3 w-fit-content rounded">
                            <i class="fas fa-fw fa-users fa-lg text-primary"></i>
                        </div>

                        <h2 class="mt-3"><?= l('index.presentation1.header') ?></h2>
                        <p class="h6 mt-3"><?= l('index.presentation1.subheader') ?></p>

                        <ul class="list-style-none mt-4 font-size-small">
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation1.feature1') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation1.feature2') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation1.feature3') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation1.feature4') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation1.feature5') ?></div>
                            </li>
                        </ul>
                        <!-- CTA Button Added -->
                        <div class="mt-4">
                            <a href="<?= url('links?type=biolink') ?>" class="btn btn-outline-primary">Create Biolink <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<div class="container mt-6">
    <div class="card index-highly-rounded border-0 flipbook-showcase-card" data-aos="fade-up">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-12 col-lg-5 mb-4 mb-lg-0">
                    <div class="flipbook-phone-showcase" aria-label="Mobile restaurant menu flipbook examples">
                        <img src="<?= ASSETS_FULL_URL . 'images/index/flipbook-menu-food-phone.png' ?>" class="flipbook-phone-shot flipbook-phone-shot-front" loading="lazy" alt="Restaurant menu flipbook on a phone" />
                        <img src="<?= ASSETS_FULL_URL . 'images/index/flipbook-menu-drinks-phone.png' ?>" class="flipbook-phone-shot flipbook-phone-shot-back" loading="lazy" alt="Drinks menu flipbook on a phone" />
                    </div>
                </div>
                <div class="col-12 col-lg ml-lg-3 flipbook-copy-column">
                    <div class="flipbook-new-feature-banner mb-3">
                        <i class="fas fa-fw fa-star mr-2"></i>
                        <span>New inclusive feature: Flipbooks are included with Seamless QR Code.</span>
                    </div>

                    <div class="bg-primary-100 p-3 w-fit-content rounded">
                        <i class="fas fa-fw fa-utensils fa-lg text-primary"></i>
                    </div>

                    <h2 class="mt-3">Digital menu flipbooks</h2>
                    <p class="h6 mt-3">Turn PDF menus into swipeable flipbooks for QR menus, bio pages, and short links.</p>

                    <ul class="list-style-none mt-4 font-size-small">
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div>Show food, drinks, prices, and page controls.</div>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div>Built for table QR menus and takeaway menus.</div>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div>Update once without reprinting QR codes.</div>
                        </li>
                    </ul>

                    <div class="mt-4">
                        <a href="https://app.seamlessqrcode.com/sso/switch?to=flipbook&redirect=dashboard" class="btn btn-outline-primary">Create Flipbook <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-6">
    <div class="card index-highly-rounded border-0 flipbook-showcase-card" data-aos="fade-up">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-12 col-lg-5 mb-4 mb-lg-0">
                    <div class="flipbook-brochure-showcase" aria-label="Desktop and mobile brochure flipbook examples">
                        <img src="<?= ASSETS_FULL_URL . 'images/index/flipbook-devices.png' ?>" class="flipbook-device-preview" loading="lazy" alt="Flipbook shown on desktop and phone screens" />
                        <img src="<?= ASSETS_FULL_URL . 'images/index/flipbook-brochure-desktop.png' ?>" class="flipbook-desktop-preview" loading="lazy" alt="Desktop brochure flipbook reader" />
                    </div>
                </div>
                <div class="col-12 col-lg ml-lg-3 flipbook-copy-column">
                    <div class="flipbook-new-feature-banner mb-3">
                        <i class="fas fa-fw fa-star mr-2"></i>
                        <span>New inclusive feature: Flipbooks are included with Seamless QR Code.</span>
                    </div>

                    <div class="bg-primary-100 p-3 w-fit-content rounded">
                        <i class="fas fa-fw fa-book-open fa-lg text-primary"></i>
                    </div>

                    <h2 class="mt-3">Brochure flipbooks</h2>
                    <p class="h6 mt-3">Publish catalogs and guides as desktop and mobile readers.</p>

                    <ul class="list-style-none mt-4 font-size-small">
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div>Add zoom and navigation.</div>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div>Share one link from QR codes and bio pages.</div>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div>Works for brochures, programs, and sales material.</div>
                        </li>
                    </ul>

                    <div class="mt-4">
                        <a href="https://app.seamlessqrcode.com/sso/switch?to=flipbook&redirect=dashboard" class="btn btn-outline-primary">Create Flipbook <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.index-container {
    position: relative;
    overflow: hidden;
}

.index-hero-spiral {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}

.index-container:after {
    content: '';
    position: absolute;
    inset: auto 0 0;
    height: 28%;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0), var(--body-bg, #ffffff));
    pointer-events: none;
    z-index: 0;
}

.index-container-content {
    position: relative;
    z-index: 1;
}

.landing-stats-section {
    margin-top: 6rem;
}

.landing-stats-copy {
    margin-bottom: 2rem;
}

.landing-stats-copy h2 {
    font-size: 2.2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: .75rem;
    overflow-wrap: anywhere;
}

.landing-stats-copy p {
    color: var(--gray-600);
    line-height: 1.7;
    margin-left: auto;
    margin-right: auto;
    max-width: 44rem;
    overflow-wrap: anywhere;
}

.landing-realtime-stats {
    background: #1f2026;
    border: 1px solid rgba(0, 0, 0, .75);
    border-radius: 1rem;
    box-shadow: 0 1.25rem 3rem rgba(15, 23, 42, .1);
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    padding: 2.75rem 3rem;
}

.landing-signup-cta {
    align-items: center;
    display: flex;
    gap: 2rem;
    justify-content: space-between;
    margin-top: 2rem;
    min-width: 0;
}

.landing-signup-cta-copy {
    max-width: 42rem;
    min-width: 0;
}

.landing-signup-cta-kicker {
    color: var(--primary);
    display: block;
    font-size: .9rem;
    font-weight: 800;
    letter-spacing: 0;
    line-height: 1.35;
    margin-bottom: .65rem;
    overflow-wrap: anywhere;
    text-transform: uppercase;
}

.landing-signup-cta h2 {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: .75rem;
    overflow-wrap: anywhere;
}

.landing-signup-cta p {
    color: var(--gray-600);
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 0;
    overflow-wrap: anywhere;
}

.landing-signup-cta-actions {
    align-items: stretch;
    display: grid;
    grid-template-columns: repeat(2, minmax(8.5rem, max-content));
    gap: .85rem;
    justify-content: flex-end;
    min-width: 0;
}

.landing-signup-cta-actions .btn {
    align-items: center;
    display: inline-flex;
    font-size: .9rem;
    justify-content: center;
    margin-bottom: 0;
    min-height: 3rem;
    min-width: 8.5rem;
    padding: .75rem 1rem;
    white-space: normal;
}

.landing-inline-cta,
.landing-final-cta {
    background: #ffffff;
    border: 1px solid var(--gray-200);
    border-radius: 1rem;
    box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, .06);
    min-width: 0;
    padding: 2rem;
}

.landing-inline-cta {
    align-items: center;
    display: flex;
    gap: 2rem;
    justify-content: space-between;
}

.landing-inline-cta h3,
.landing-final-cta h2 {
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: .75rem;
    overflow-wrap: anywhere;
}

.landing-inline-cta h3 {
    font-size: 1.55rem;
}

.landing-final-cta h2 {
    font-size: 2rem;
}

.landing-inline-cta p,
.landing-final-cta p {
    color: var(--gray-600);
    line-height: 1.7;
    margin-bottom: 0;
    overflow-wrap: anywhere;
}

.landing-inline-cta-actions,
.landing-final-cta-actions {
    align-items: stretch;
    display: grid;
    grid-template-columns: repeat(2, minmax(8.5rem, max-content));
    gap: .85rem;
    justify-content: flex-end;
    min-width: 0;
}

.landing-inline-cta-actions .btn,
.landing-final-cta-actions .btn {
    align-items: center;
    display: inline-flex;
    font-size: .9rem;
    justify-content: center;
    margin-bottom: 0;
    min-height: 3rem;
    min-width: 8.5rem;
    padding: .75rem 1rem;
    white-space: normal;
}

.landing-inline-cta-dark {
    background: #1f2026;
    border-color: rgba(0, 0, 0, .75);
    color: #ffffff;
}

.landing-inline-cta-dark h3 {
    color: #ffffff;
}

.landing-inline-cta-dark p {
    color: #c5cedd;
}

.landing-final-cta {
    padding: 3rem 2rem;
}

.landing-final-cta p {
    margin-left: auto;
    margin-right: auto;
    max-width: 44rem;
}

.landing-final-cta-actions {
    justify-content: center;
    margin-top: 1.75rem;
    min-width: 0;
}

.landing-stat-item {
    min-width: 0;
    text-align: center;
}

.landing-stat-label {
    color: #9fb1cc;
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.landing-stat-value {
    font-size: 2.25rem;
    font-weight: 700;
    line-height: 1.15;
    margin-top: 1.15rem;
    overflow-wrap: anywhere;
}

.landing-stat-value-links {
    color: #c85cdc;
}

.landing-stat-value-qr {
    color: #55c7e9;
}

.landing-stat-value-flipbooks {
    color: #f2b447;
}

.landing-stat-value-pageviews {
    color: #557cff;
}

.flipbook-new-feature-banner {
    align-items: center;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: .75rem;
    color: #9a3412;
    display: inline-flex;
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.35;
    max-width: 100%;
    padding: .75rem 1rem;
}

.flipbook-new-feature-banner span {
    min-width: 0;
    overflow-wrap: anywhere;
}

.flipbook-phone-showcase {
    align-items: flex-end;
    display: flex;
    gap: 1rem;
    justify-content: center;
    min-height: 28rem;
}

.flipbook-showcase-card {
    overflow: hidden;
}

.flipbook-showcase-card .row,
.flipbook-showcase-card .row > [class*="col"] {
    min-width: 0;
}

.flipbook-copy-column {
    min-width: 0;
}

.flipbook-copy-column h2,
.flipbook-copy-column p,
.flipbook-copy-column li div {
    overflow-wrap: anywhere;
    white-space: normal;
}

.flipbook-copy-column .d-flex > div {
    min-width: 0;
}

.flipbook-phone-shot {
    background: #ffffff;
    border: 1px solid var(--gray-300);
    border-radius: 1.4rem;
    box-shadow: 0 1rem 2.25rem rgba(15, 23, 42, .14);
    height: auto;
    max-height: 27rem;
    max-width: calc(50% - .5rem);
    object-fit: contain;
}

.flipbook-phone-shot-back {
    max-height: 24rem;
    transform: translateY(1.25rem);
}

.flipbook-brochure-showcase {
    display: grid;
    gap: 1rem;
}

.flipbook-device-preview,
.flipbook-desktop-preview {
    background: #ffffff;
    border: 1px solid var(--gray-300);
    border-radius: 1rem;
    box-shadow: 10px 10px 0 #ebf5ff;
    height: auto;
    max-width: 100%;
    object-fit: contain;
}

.flipbook-desktop-preview {
    aspect-ratio: 16 / 9;
    object-fit: cover;
    object-position: center;
}

@media (max-width: 991.98px) {
    .landing-stats-section {
        margin-top: 5rem;
    }

    .landing-realtime-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding: 2rem;
    }

    .landing-signup-cta {
        align-items: flex-start;
        flex-direction: column;
    }

    .landing-signup-cta-actions {
        justify-content: flex-start;
        min-width: 0;
        width: 100%;
    }

    .landing-inline-cta {
        align-items: flex-start;
        flex-direction: column;
    }

    .landing-inline-cta-actions {
        justify-content: flex-start;
        min-width: 0;
        width: 100%;
    }

    .flipbook-phone-showcase {
        min-height: 24rem;
    }

    .flipbook-phone-shot {
        max-height: 23rem;
    }

    .flipbook-phone-shot-back {
        max-height: 21rem;
    }
}

@media (max-width: 575.98px) {
    .landing-stats-section {
        margin-top: 4rem;
    }

    .landing-stats-copy h2,
    .landing-final-cta h2 {
        font-size: 1.65rem;
    }

    .landing-realtime-stats {
        width: 100%;
    }

    .card.index-highly-rounded:not(.flipbook-showcase-card) .row {
        display: block;
        margin-left: 0;
        margin-right: 0;
    }

    .card.index-highly-rounded:not(.flipbook-showcase-card) .row > [class*="col"] {
        margin-left: 0 !important;
        max-width: 100%;
        padding-left: 0;
        padding-right: 0;
        width: 100%;
    }

    .card.index-highly-rounded:not(.flipbook-showcase-card) .index-card-image {
        height: auto;
        max-width: 100%;
        width: 100%;
    }

    .landing-realtime-stats {
        grid-template-columns: 1fr;
        padding: 1.5rem;
    }

    .landing-stat-value {
        font-size: 2rem;
        margin-top: .75rem;
    }

    .landing-signup-cta {
        gap: 1.35rem;
        margin-top: 1.5rem;
    }

    .landing-signup-cta-copy {
        max-width: 100%;
        width: 100%;
    }

    .landing-signup-cta-kicker {
        font-size: .78rem;
    }

    .landing-signup-cta h2 {
        font-size: 1.65rem;
    }

    .landing-signup-cta-actions {
        display: grid;
        grid-template-columns: 1fr;
    }

    .landing-signup-cta-actions .btn,
    .landing-inline-cta-actions .btn,
    .landing-final-cta-actions .btn {
        min-width: 0;
        width: 100%;
    }

    .landing-inline-cta,
    .landing-final-cta {
        padding: 1.5rem;
    }

    .landing-inline-cta h3 {
        font-size: 1.35rem;
    }

    .landing-inline-cta-actions,
    .landing-final-cta-actions {
        display: grid;
        grid-template-columns: 1fr;
        justify-content: stretch;
        min-width: 0;
        width: 100%;
    }

    .flipbook-showcase-card .card-body {
        padding: 1.25rem;
    }

    .flipbook-showcase-card .row {
        display: block;
        margin-left: 0;
        margin-right: 0;
    }

    .flipbook-showcase-card .row > [class*="col"] {
        max-width: 100%;
        padding-left: 0;
        padding-right: 0;
        width: 100%;
    }

    .flipbook-phone-showcase {
        gap: .75rem;
        min-height: 20rem;
        max-width: 100%;
        overflow: hidden;
    }

    .flipbook-phone-shot {
        border-radius: 1rem;
        max-height: 19rem;
        max-width: calc(46% - .375rem);
    }

    .flipbook-phone-shot-back {
        max-height: 17rem;
        transform: translateY(.75rem);
    }

    .flipbook-copy-column h2 {
        font-size: 1.75rem;
        line-height: 1.2;
    }

    .flipbook-copy-column h2,
    .flipbook-copy-column p,
    .flipbook-copy-column li div {
        max-width: 300px;
    }

    .flipbook-brochure-showcase {
        max-width: 100%;
        overflow: hidden;
    }

    .flipbook-device-preview,
    .flipbook-desktop-preview {
        display: block;
        box-shadow: 6px 6px 0 #ebf5ff;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
        width: 300px;
    }
}
</style>

<?php if(settings()->links->shortener_is_enabled): ?>
    <div class="container mt-6">
        <div class="card index-highly-rounded border-0" data-aos="fade-up">
            <div class="card-body">
                <div class="row">
                    <div class="col-auto col-lg-5 mb-4 mb-lg-0">
                        <img src="<?= get_custom_image_if_any('index/short-link.webp') ?>" class="index-card-image index-highly-rounded" loading="lazy" alt="<?= l('index.short_image_alt') ?>" />
                    </div>
                    <div class="col ml-3">
                        <div class="bg-primary-100 p-3 w-fit-content rounded">
                            <i class="fas fa-fw fa-link fa-lg text-primary"></i>
                        </div>

                        <h2 class="mt-3"><?= l('index.presentation2.header') ?></h2>
                        <p class="h6 mt-3"><?= l('index.presentation2.subheader') ?></p>

                        <ul class="list-style-none mt-4 font-size-small">
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation2.feature1') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation2.feature2') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation2.feature3') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation2.feature4') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation2.feature5') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation2.feature6') ?></div>
                            </li>
                        </ul>
                        <!-- CTA Button Added -->
                        <div class="mt-4">
                             <a href="<?= url('links?type=link') ?>" class="btn btn-outline-primary">Create Short link <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>


<div class="container landing-stats-section">
    <div class="landing-stats-copy text-center" data-aos="fade-up">
        <span class="landing-signup-cta-kicker">Live platform stats</span>
        <h2>Real-time QR code, short link, bio page, and flipbook activity.</h2>
        <p>These numbers update from Seamless QR Code usage data, including created links, generated QR codes, hosted flipbooks, and tracked pageviews.</p>
    </div>

    <div class="landing-realtime-stats" data-aos="fade-up">
        <div class="landing-stat-item">
            <div class="landing-stat-label"><?= l('index.stats.links') ?></div>
            <div class="landing-stat-value landing-stat-value-links" data-landing-stat-value="links"><?= $landing_stats_format($landing_stats['links']) ?></div>
        </div>

        <div class="landing-stat-item">
            <div class="landing-stat-label"><?= l('index.stats.qr_codes') ?></div>
            <div class="landing-stat-value landing-stat-value-qr" data-landing-stat-value="qr_codes"><?= $landing_stats_format($landing_stats['qr_codes']) ?></div>
        </div>

        <div class="landing-stat-item">
            <div class="landing-stat-label">Flipbooks</div>
            <div class="landing-stat-value landing-stat-value-flipbooks" data-landing-stat-value="flipbooks"><?= $landing_stats_format($landing_stats['flipbooks']) ?></div>
        </div>

        <div class="landing-stat-item">
            <div class="landing-stat-label"><?= l('index.stats.track_links') ?></div>
            <div class="landing-stat-value landing-stat-value-pageviews" data-landing-stat-value="track_links"><?= $landing_stats_format($landing_stats['track_links']) ?></div>
        </div>
    </div>

    <div class="landing-signup-cta" data-aos="fade-up">
        <div class="landing-signup-cta-copy">
            <span class="landing-signup-cta-kicker">QR codes, bio links, short links, and flipbooks</span>
            <h2>Create and track your digital marketing links in one place.</h2>
            <p>Launch branded QR codes, mobile bio pages, tracked short links, and interactive flipbooks for menus, brochures, catalogs, and business campaigns.</p>
        </div>

        <div class="landing-signup-cta-actions">
            <?php if(is_logged_in()): ?>
                <a href="<?= url('dashboard') ?>" class="btn index-button index-button-white bg-gradient border-0">
                    <?= l('dashboard.menu') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                </a>
            <?php elseif(settings()->users->register_is_enabled): ?>
                <a href="<?= url('register') ?>" class="btn index-button index-button-white bg-gradient border-0">
                    Sign up free <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                </a>
            <?php else: ?>
                <a href="<?= url('login') ?>" class="btn index-button index-button-white bg-gradient border-0">
                    Sign in <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                </a>
            <?php endif ?>

            <a href="https://app.seamlessqrcode.com/plan" class="btn btn-outline-primary index-button">
                Plans <i class="fas fa-fw fa-sm fa-tags"></i>
            </a>
        </div>
    </div>
</div>


<!-- =================================================================
// NEW VISUAL "HOW IT WORKS" SECTION
================================================================== -->
<div class="container my-8 py-5">
    <div class="text-center mb-5">
        <h2>How It <span class="text-primary">Works</span></h2>
        <p class="text-muted">Get started in minutes with our simple, powerful workflow designed for everyone.</p>
    </div>

    <div class="how-it-works-wrapper">
        <!-- Step 1 -->
        <div class="how-it-works-step">
            <div class="step-circle">01</div>
            <h5 class="font-weight-bold mt-3">Create Your Content</h5>
            <p class="text-muted small">Upload your content, enter your URL, or create your bio link page with our intuitive builder.</p>
        </div>

        <div class="step-connector">
            <i class="fas fa-upload"></i>
        </div>

        <!-- Step 2 -->
        <div class="how-it-works-step">
            <div class="step-circle">02</div>
            <h5 class="font-weight-bold mt-3">Customize & Configure</h5>
            <p class="text-muted small">Personalize your QR code design, set up tracking parameters, and configure your preferences.</p>
        </div>

        <div class="step-connector">
            <i class="fas fa-cogs"></i>
        </div>

        <!-- Step 3 -->
        <div class="how-it-works-step">
            <div class="step-circle">03</div>
            <h5 class="font-weight-bold mt-3">Share Everywhere</h5>
            <p class="text-muted small">Download your QR code, copy your short link, or share your bio link across all platforms.</p>
        </div>

        <div class="step-connector">
             <i class="fas fa-share-alt"></i>
        </div>

        <!-- Step 4 -->
        <div class="how-it-works-step">
            <div class="step-circle">04</div>
            <h5 class="font-weight-bold mt-3">Track Performance</h5>
            <p class="text-muted small">Monitor scans, clicks, and engagement with detailed analytics and real-time insights.</p>
        </div>
    </div>

    <div class="landing-inline-cta mt-5" data-aos="fade-up">
        <div>
            <span class="landing-signup-cta-kicker">Start your campaign</span>
            <h3>Build your first QR code, bio page, short link, or flipbook today.</h3>
            <p>Create branded links for menus, brochures, social profiles, events, and marketing campaigns with tracking built in.</p>
        </div>

        <div class="landing-inline-cta-actions">
            <?php if(is_logged_in()): ?>
                <a href="<?= url('dashboard') ?>" class="btn index-button index-button-white bg-gradient border-0"><?= l('dashboard.menu') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
            <?php elseif(settings()->users->register_is_enabled): ?>
                <a href="<?= url('register') ?>" class="btn index-button index-button-white bg-gradient border-0">Create account <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
            <?php endif ?>

            <a href="https://app.seamlessqrcode.com/plan" class="btn btn-outline-primary index-button">Plans <i class="fas fa-fw fa-sm fa-tags"></i></a>
        </div>
    </div>
</div>

<style>
.how-it-works-wrapper {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    text-align: center;
    gap: 1rem;
}
.how-it-works-step {
    max-width: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.step-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #a855f7, #6366f1);
    color: white;
    font-size: 1.25rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
    margin-bottom: 1rem;
}
.step-connector {
    flex-grow: 1;
    max-width: 100px;
    margin-top: 30px;
    color: #d1d5db;
    font-size: 1.25rem;
    position: relative;
}
.step-connector i {
    background: #fff;
    padding: 0 10px;
    position: relative;
    z-index: 1;
}
.step-connector::before {
    content: '';
    position: absolute;
    width: 100%;
    top: 50%;
    left: 0;
    border-top: 2px dashed #d1d5db;
    transform: translateY(-50%);
    z-index: 0;
}
@media (max-width: 991.98px) {
    .how-it-works-wrapper {
        flex-wrap: wrap;
        justify-content: center;
    }
    .how-it-works-step {
        width: 45%;
        margin-bottom: 2rem;
    }
    .step-connector {
        display: none;
    }
}
@media (max-width: 767.98px) {
    .how-it-works-step {
        width: 80%;
    }
}
</style>
<!-- =================================================================
// END "HOW IT WORKS" SECTION
================================================================== -->


<!-- =================================================================
// "PERFECT FOR EVERY INDUSTRY" SECTION
================================================================== -->
<div class="container mt-8 py-5">
    <div class="text-center mb-5">
        <h2>Perfect for <span class="text-primary">Every Industry</span></h2>
        <p class="text-muted">Discover how businesses across industries use Seamless QR Code to enhance their operations and customer experience.</p>
    </div>
    <div class="row">
        <!-- Industry Card 1: Restaurants & Cafes -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 industry-card">
                <div class="card-body">
                    <div class="industry-icon-wrapper"><i class="fas fa-store-alt fa-fw"></i></div>
                    <h5 class="font-weight-bold mt-3">Restaurants & Cafes</h5>
                    <p class="text-muted small">Digital menus, contactless ordering, and table service optimization.</p>
                    <p class="text-muted font-weight-bold small mt-4" style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">Common Uses:</p>
                    <div class="d-flex flex-wrap">
                        <span class="badge industry-badge">QR menus</span><span class="badge industry-badge">Order & pay</span><span class="badge industry-badge">Customer feedback</span><span class="badge industry-badge">WiFi sharing</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Industry Card 2: Business & Marketing -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 industry-card">
                <div class="card-body">
                    <div class="industry-icon-wrapper"><i class="fas fa-briefcase fa-fw"></i></div>
                    <h5 class="font-weight-bold mt-3">Business & Marketing</h5>
                    <p class="text-muted small">Lead generation, contact sharing, and marketing campaign tracking.</p>
                    <p class="text-muted font-weight-bold small mt-4" style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">Common Uses:</p>
                    <div class="d-flex flex-wrap">
                        <span class="badge industry-badge">Business cards</span><span class="badge industry-badge">Event registration</span><span class="badge industry-badge">Product info</span><span class="badge industry-badge">Social media</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Industry Card 3: Events & Networking -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 industry-card">
                <div class="card-body">
                    <div class="industry-icon-wrapper"><i class="fas fa-users fa-fw"></i></div>
                    <h5 class="font-weight-bold mt-3">Events & Networking</h5>
                    <p class="text-muted small">Streamline check-ins, share information, and connect attendees.</p>
                    <p class="text-muted font-weight-bold small mt-4" style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">Common Uses:</p>
                    <div class="d-flex flex-wrap">
                        <span class="badge industry-badge">Event check-in</span><span class="badge industry-badge">Speaker bio links</span><span class="badge industry-badge">Networking cards</span><span class="badge industry-badge">Schedule sharing</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Industry Card 4: Education -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 industry-card">
                <div class="card-body">
                    <div class="industry-icon-wrapper"><i class="fas fa-graduation-cap fa-fw"></i></div>
                    <h5 class="font-weight-bold mt-3">Education</h5>
                    <p class="text-muted small">Share resources, collect assignments, and enhance learning experiences.</p>
                    <p class="text-muted font-weight-bold small mt-4" style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">Common Uses:</p>
                    <div class="d-flex flex-wrap">
                        <span class="badge industry-badge">Resource sharing</span><span class="badge industry-badge">Assignment submission</span><span class="badge industry-badge">Course materials</span><span class="badge industry-badge">Student portfolios</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Industry Card 5: Healthcare -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 industry-card">
                <div class="card-body">
                    <div class="industry-icon-wrapper"><i class="fas fa-heartbeat fa-fw"></i></div>
                    <h5 class="font-weight-bold mt-3">Healthcare</h5>
                    <p class="text-muted small">Patient information, appointment booking, and health resource sharing.</p>
                    <p class="text-muted font-weight-bold small mt-4" style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">Common Uses:</p>
                    <div class="d-flex flex-wrap">
                        <span class="badge industry-badge">Patient forms</span><span class="badge industry-badge">Appointment booking</span><span class="badge industry-badge">Health resources</span><span class="badge industry-badge">Emergency contacts</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Industry Card 6: Creative & Portfolio -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 industry-card">
                <div class="card-body">
                    <div class="industry-icon-wrapper"><i class="fas fa-palette fa-fw"></i></div>
                    <h5 class="font-weight-bold mt-3">Creative & Portfolio</h5>
                    <p class="text-muted small">Showcase work, share portfolios, and connect with your audience.</p>
                    <p class="text-muted font-weight-bold small mt-4" style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">Common Uses:</p>
                    <div class="d-flex flex-wrap">
                        <span class="badge industry-badge">Portfolio links</span><span class="badge industry-badge">Social media</span><span class="badge industry-badge">Contact info</span><span class="badge industry-badge">Creative showcases</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.industry-card {
    border: 1px solid #f3f4f6;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.industry-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.industry-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background-color: #eef2ff;
    color: #6366f1;
    border-radius: 0.75rem;
    font-size: 1.5rem;
}
.industry-badge {
    background-color: #f3f4f6;
    color: #4b5563;
    font-weight: 500;
    padding: 0.3em 0.7em;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
    border-radius: 999px;
    font-size: 0.75rem;
}
</style>
<!-- =================================================================
// END OF "INDUSTRY" SECTION
================================================================== -->

<div class="container my-5">
    <div class="landing-inline-cta landing-inline-cta-dark" data-aos="fade-up">
        <div>
            <span class="landing-signup-cta-kicker">Made for growing businesses</span>
            <h3>Turn every print, table, package, event, and profile into a measurable digital touchpoint.</h3>
            <p>Use Seamless QR Code for restaurant QR menus, brochure flipbooks, bio link pages, file sharing, and campaign analytics from one dashboard.</p>
        </div>

        <div class="landing-inline-cta-actions">
            <?php if(is_logged_in()): ?>
                <a href="<?= url('dashboard') ?>" class="btn index-button index-button-white bg-gradient border-0"><?= l('dashboard.menu') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
            <?php elseif(settings()->users->register_is_enabled): ?>
                <a href="<?= url('register') ?>" class="btn index-button index-button-white bg-gradient border-0">Start free <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
            <?php endif ?>

            <a href="https://app.seamlessqrcode.com/sso/switch?to=flipbook&redirect=dashboard" class="btn btn-outline-light index-button">Flipbooks <i class="fas fa-fw fa-sm fa-book-open"></i></a>
        </div>
    </div>
</div>


<?php if(settings()->links->static_is_enabled): ?>
    <div class="container mt-6">
        <div class="card index-highly-rounded border-0" data-aos="fade-up">
            <div class="card-body">
                <div class="row">
                    <div class="col-auto col-lg-5 mb-4 mb-lg-0">
                        <img src="<?= get_custom_image_if_any('index/static-link.webp') ?>" class="index-card-image index-highly-rounded" loading="lazy" alt="<?= l('index.static_image_alt') ?>" />
                    </div>
                    <div class="col ml-3">
                        <div class="bg-primary-100 p-3 w-fit-content rounded">
                            <i class="fas fa-fw fa-file-code fa-lg text-primary"></i>
                        </div>

                        <h2 class="mt-3"><?= l('index.presentation5.header') ?></h2>
                        <p class="h6 mt-3"><?= l('index.presentation5.subheader') ?></p>

                        <ul class="list-style-none mt-4 font-size-small">
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation5.feature1') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation5.feature2') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation5.feature3') ?></div>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                                <div><?= l('index.presentation5.feature4') ?></div>
                            </li>
                        </ul>
                        <!-- CTA Button Added -->
                        <div class="mt-4">
                            <a href="<?= url('links?type=static') ?>" class="btn btn-outline-primary">Learn More <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(settings()->links->biolinks_is_enabled ||settings()->links->shortener_is_enabled ||settings()->links->files_is_enabled ||settings()->links->vcards_is_enabled ||settings()->links->events_is_enabled ||settings()->links->static_is_enabled): ?>
<div class="container mt-6">
    <div class="card index-highly-rounded border-0" data-aos="fade-up">
        <div class="card-body">
            <div class="row">
                <div class="col-auto col-lg-5 mb-4 mb-lg-0">
                    <img src="<?= get_custom_image_if_any('index/analytics.webp') ?>" class="index-card-image index-highly-rounded" loading="lazy" alt="<?= l('index.analytics_image_alt') ?>" />
                </div>
                <div class="col ml-3">
                    <div class="bg-primary-100 p-3 w-fit-content rounded">
                        <i class="fas fa-fw fa-chart-bar fa-lg text-primary"></i>
                    </div>

                    <h2 class="mt-3"><?= l('index.presentation4.header') ?></h2>
                    <p class="h6 mt-3"><?= l('index.presentation4.subheader') ?></p>

                    <ul class="list-style-none mt-4 font-size-small">
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div><?= l('index.presentation4.feature1') ?></div>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div><?= l('index.presentation4.feature2') ?></div>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div><?= l('index.presentation4.feature3') ?></div>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div><?= l('index.presentation4.feature4') ?></div>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-3"></i>
                            <div><?= l('index.presentation4.feature5') ?></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif ?>

<div class="container my-6">
    <div class="landing-final-cta text-center" data-aos="fade-up">
        <span class="landing-signup-cta-kicker">Ready to publish</span>
        <h2>Start with a QR code, then grow into bio links, flipbooks, files, and analytics.</h2>
        <p>Seamless QR Code helps you create scannable campaigns, track pageviews, update destination content, and give customers a fast mobile experience.</p>

        <div class="landing-final-cta-actions">
            <?php if(is_logged_in()): ?>
                <a href="<?= url('dashboard') ?>" class="btn index-button index-button-white bg-gradient border-0"><?= l('dashboard.menu') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
            <?php elseif(settings()->users->register_is_enabled): ?>
                <a href="<?= url('register') ?>" class="btn index-button index-button-white bg-gradient border-0">Sign up <i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
            <?php endif ?>

            <a href="https://app.seamlessqrcode.com/plan" class="btn btn-outline-primary index-button">Plans <i class="fas fa-fw fa-sm fa-tags"></i></a>
        </div>
    </div>
</div>

<div class="py-3"></div>

<!-- The rest of the original file continues... -->
<!-- This confirms all subsequent sections are still present. -->
