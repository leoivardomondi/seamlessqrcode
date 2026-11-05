<?php
defined('ALTUMCODE') || die();

$settings = $data->splash_page->settings ?? (object)[];
$link_id = $this->link->link_id ?? 0;
$destination_url = $this->link->full_url ?? url();
$primary_btn_name = !empty($settings->primary_button_name) ? $settings->primary_button_name : l('link.splash.continue');
$has_details_form = !empty($settings->details_form_is_enabled);

/* Background CSS generation */
$bg_css = '';
if(($settings->background_type ?? 'default') == 'color' && !empty($settings->background_color)) {
    $bg_css = "background-color: " . $settings->background_color . " !important;";
} elseif(($settings->background_type ?? 'default') == 'gradient' && !empty($settings->background_color_one) && !empty($settings->background_color_two)) {
    $direction = $settings->background_gradient_direction ?? '135deg';
    $bg_css = "background: linear-gradient(" . $direction . ", " . $settings->background_color_one . ", " . $settings->background_color_two . ") !important; background-attachment: fixed !important;";
} elseif(($settings->background_type ?? 'default') == 'image' && !empty($settings->background)) {
    $bg_css = "background: url('" . \Altum\Uploads::get_full_url('splash_pages') . $settings->background . "') no-repeat center center / cover !important; background-attachment: fixed !important;";
}
?>

<style>
    <?php if(!empty($bg_css)): ?>
    body, html {
        <?= $bg_css ?>
        min-height: 100vh;
    }
    <?php endif ?>

    <?php if(!empty($settings->title_color)): ?>
    .splash-title {
        color: <?= $settings->title_color ?> !important;
    }
    <?php endif ?>

    <?php if(!empty($settings->description_color)): ?>
    .splash-description {
        color: <?= $settings->description_color ?> !important;
    }
    <?php endif ?>

    .btn-splash-primary {
        <?php if(!empty($settings->primary_button_background_color)): ?>
        background-color: <?= $settings->primary_button_background_color ?> !important;
        border-color: <?= $settings->primary_button_background_color ?> !important;
        <?php endif ?>
        <?php if(!empty($settings->primary_button_text_color)): ?>
        color: <?= $settings->primary_button_text_color ?> !important;
        <?php endif ?>
    }

    .btn-splash-secondary {
        <?php if(!empty($settings->secondary_button_background_color)): ?>
        background-color: <?= $settings->secondary_button_background_color ?> !important;
        border-color: <?= $settings->secondary_button_background_color ?> !important;
        <?php endif ?>
        <?php if(!empty($settings->secondary_button_text_color)): ?>
        color: <?= $settings->secondary_button_text_color ?> !important;
        <?php endif ?>
    }

    /* Form Card Styling - Wider & Reduced Height */
    .splash-form-card {
        background-color: <?= $settings->form_card_background_color ?? '#18181b' ?> !important;
        color: <?= $settings->form_card_text_color ?? '#f4f4f5' ?> !important;
        border: 1px solid <?= $settings->form_card_border_color ?? '#27272a' ?> !important;
        border-radius: 1rem;
        box-shadow: 0 16px 28px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -5px rgba(0, 0, 0, 0.3);
        padding: 1.15rem 1.35rem !important;
        text-align: left;
    }

    .splash-form-card .form-label-caps {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
        color: <?= $settings->form_card_text_color ?? '#f4f4f5' ?>;
        opacity: 0.8;
        margin-bottom: 0.2rem !important;
        display: block;
    }

    .splash-form-card .form-control {
        background-color: rgba(255, 255, 255, 0.06) !important;
        border: 1px solid rgba(255, 255, 255, 0.16) !important;
        color: <?= $settings->form_card_text_color ?? '#ffffff' ?> !important;
        border-radius: 0.5rem !important;
        height: 38px !important;
        min-height: 38px !important;
        padding: 0.35rem 0.85rem !important;
        font-size: 0.9rem !important;
        transition: all 0.2s ease-in-out;
    }

    .splash-form-card .form-control:focus {
        background-color: rgba(255, 255, 255, 0.1) !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 0 0 0.15rem rgba(255, 255, 255, 0.1) !important;
    }

    .splash-form-card .form-control::placeholder {
        color: <?= $settings->form_card_text_color ?? '#ffffff' ?>;
        opacity: 0.4;
    }

    .splash-form-card .custom-control-label {
        font-size: 0.82rem !important;
        color: <?= $settings->form_card_text_color ?? '#ffffff' ?>;
        opacity: 0.9;
        cursor: pointer;
        line-height: 1.35 !important;
    }

    .splash-form-card .custom-checkbox .custom-control-label::before {
        border-radius: 0.25rem;
        border-color: rgba(255, 255, 255, 0.3) !important;
        background-color: rgba(255, 255, 255, 0.06) !important;
    }

    .splash-form-card .custom-control-input:checked ~ .custom-control-label::before {
        background-color: <?= $settings->form_submit_button_background_color ?? '#eab308' ?> !important;
        border-color: <?= $settings->form_submit_button_background_color ?? '#eab308' ?> !important;
    }

    /* Steady, standard single-line submit button across all devices */
    .btn-splash-submit {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 44px !important;
        min-height: 44px !important;
        max-height: 44px !important;
        padding: 0 1.25rem !important;
        font-size: 0.92rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1 !important;
        border: none !important;
        box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.25) !important;
        transition: transform 0.15s ease, opacity 0.15s ease, filter 0.15s ease !important;
        box-sizing: border-box !important;
    }
    .btn-splash-submit:hover {
        filter: brightness(1.05);
    }
    .btn-splash-submit:active {
        transform: scale(0.99);
    }

    /* Skip Link */
    .btn-splash-skip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        text-decoration: none !important;
        opacity: 0.8;
        padding: 0.35rem 0.6rem;
        transition: opacity 0.2s ease, transform 0.15s ease;
    }
    .btn-splash-skip:hover {
        opacity: 1;
        transform: translateX(2px);
    }

    /* Full-width standard secondary home button */
    .btn-splash-secondary-block {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 40px !important;
        min-height: 40px !important;
        max-height: 40px !important;
        padding: 0 1.25rem !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        box-sizing: border-box !important;
        text-decoration: none !important;
        <?php if(!empty($settings->secondary_button_background_color)): ?>
        background-color: <?= $settings->secondary_button_background_color ?> !important;
        border: 1px solid <?= $settings->secondary_button_background_color ?> !important;
        <?php else: ?>
        background-color: #27272a !important;
        border: 1px solid #3f3f46 !important;
        <?php endif ?>
        <?php if(!empty($settings->secondary_button_text_color)): ?>
        color: <?= $settings->secondary_button_text_color ?> !important;
        <?php else: ?>
        color: #ffffff !important;
        <?php endif ?>
        transition: filter 0.15s ease;
    }
    .btn-splash-secondary-block:hover {
        filter: brightness(1.15);
        color: <?= $settings->secondary_button_text_color ?? '#ffffff' ?> !important;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-11 col-lg-9 py-3 py-md-4">
            <?= \Altum\Alerts::output_alerts() ?>

            <div class="text-center">
                <?php if(!empty($data->splash_page->settings->logo)): ?>
                    <div class="d-flex flex-column align-items-center mb-3">
                        <img src="<?= \Altum\Uploads::get_full_url('splash_pages') . $data->splash_page->settings->logo ?>" class="link-image link-avatar-round" style="width: 80px; height: 80px; object-fit: cover;" alt="<?= $data->splash_page->name ?>" />
                    </div>
                <?php endif ?>

                <?php if(!empty($data->splash_page->title)): ?>
                    <h1 class="h4 splash-title mb-1 font-weight-bold"><?= $data->splash_page->title ?></h1>
                <?php endif ?>

                <?php if(!empty($data->splash_page->description)): ?>
                    <div class="splash-description text-muted mb-3" style="max-width: 580px; width: 100%; margin-left: auto; margin-right: auto; line-height: 1.4; font-size: 0.95rem;">
                        <?= $data->splash_page->description ?>
                    </div>
                <?php endif ?>

                <?php if($has_details_form): ?>
                    <!-- Optional Customer Details Capture Feature -->
                    <?php if(!empty($settings->form_title)): ?>
                        <div class="splash-title h6 font-weight-normal mb-2" style="max-width: 580px; width: 100%; margin-left: auto; margin-right: auto; line-height: 1.4;">
                            <?= $settings->form_title ?>
                        </div>
                    <?php endif ?>

                    <?php if(!empty($settings->form_subtitle)): ?>
                        <div class="splash-description small text-muted mb-2" style="max-width: 580px; width: 100%; margin-left: auto; margin-right: auto;">
                            <?= $settings->form_subtitle ?>
                        </div>
                    <?php endif ?>

                    <form id="splash_details_form" method="post" role="form" style="max-width: 580px; width: 100%; margin-left: auto; margin-right: auto;">
                        <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                        <input type="hidden" name="link_id" value="<?= $link_id ?>" />
                        <input type="hidden" name="splash_page_id" value="<?= $data->splash_page->splash_page_id ?? 0 ?>" />

                        <div class="notification-container mb-2"></div>

                        <div class="splash-form-card mb-2">
                            <?php if($settings->form_show_name ?? true): ?>
                                <div class="form-group mb-2">
                                    <label for="splash_name" class="form-label-caps">
                                        <?= $settings->form_name_label ?: l('splash_pages.form_name_label_default') ?>
                                    </label>
                                    <input type="text" id="splash_name" name="name" class="form-control" placeholder="<?= $settings->form_name_placeholder ?: l('splash_pages.form_name_placeholder_default') ?>" <?= !empty($settings->form_name_required) ? 'required="required"' : null ?> maxlength="128" />
                                </div>
                            <?php endif ?>

                            <?php if($settings->form_show_phone ?? true): ?>
                                <div class="form-group mb-2">
                                    <label for="splash_phone" class="form-label-caps">
                                        <?= $settings->form_phone_label ?: l('splash_pages.form_phone_label_default') ?>
                                    </label>
                                    <input type="tel" id="splash_phone" name="phone" class="form-control" placeholder="<?= $settings->form_phone_placeholder ?: l('splash_pages.form_phone_placeholder_default') ?>" <?= !empty($settings->form_phone_required) ? 'required="required"' : null ?> maxlength="64" />
                                </div>
                            <?php endif ?>

                            <?php if($settings->form_show_email ?? true): ?>
                                <div class="form-group mb-2">
                                    <label for="splash_email" class="form-label-caps">
                                        <?= $settings->form_email_label ?: l('splash_pages.form_email_label_default') ?>
                                    </label>
                                    <input type="email" id="splash_email" name="email" class="form-control" placeholder="<?= $settings->form_email_placeholder ?: l('splash_pages.form_email_placeholder_default') ?>" <?= !empty($settings->form_email_required) ? 'required="required"' : null ?> maxlength="320" />
                                </div>
                            <?php endif ?>

                            <?php if($settings->form_show_consent ?? true): ?>
                                <div class="form-group custom-control custom-checkbox mt-2 mb-0">
                                    <input type="checkbox" id="splash_consent" name="consent" class="custom-control-input" value="1" <?= !empty($settings->form_consent_required) ? 'required="required"' : null ?> />
                                    <label class="custom-control-label" for="splash_consent">
                                        <?= $settings->form_consent_text ?: l('splash_pages.form_consent_text_default') ?>
                                    </label>
                                </div>
                            <?php endif ?>
                        </div>

                        <!-- Steady standard single-line submit button -->
                        <button type="submit" class="btn btn-block btn-splash-submit mb-2 <?= $settings->primary_button_border_radius ?? 'rounded' ?>" style="background-color: <?= $settings->form_submit_button_background_color ?? '#eab308' ?>; color: <?= $settings->form_submit_button_text_color ?? '#000000' ?>;">
                            <?= $settings->form_submit_button_text ?: l('splash_pages.form_submit_button_text_default') ?>
                        </button>

                        <!-- Skip button directly below submit button -->
                        <div class="text-center mb-1">
                            <a href="#" id="splash_skip_button" class="btn-splash-skip" style="color: <?= $settings->description_color ?? '#a1a1aa' ?>;">
                                <?= $settings->form_skip_button_text ?: l('splash_pages.form_skip_button_text_default') ?> <i class="fas fa-fw fa-xs fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </form>

                    <?php if(!empty($settings->secondary_button_url)): ?>
                        <div style="max-width: 580px; width: 100%; margin-left: auto; margin-right: auto;" class="mt-3">
                            <a href="<?= $settings->secondary_button_url ?>" class="btn btn-block btn-splash-secondary-block <?= $settings->secondary_button_border_radius ?? 'rounded' ?>">
                                <i class="fas fa-fw fa-sm fa-home mr-2"></i>
                                <?= $settings->secondary_button_name ?: l('link.splash.home') ?>
                            </a>
                        </div>
                    <?php endif ?>

                <?php else: ?>
                    <!-- Standard Splash Page (Timer + Continue) -->
                    <form style="max-width: 580px; width: 100%; margin-left: auto; margin-right: auto;">
                        <div class="row mt-3 justify-content-center">
                            <?php if(!empty($settings->secondary_button_url)): ?>
                                <div class="col-6">
                                    <a href="<?= $settings->secondary_button_url ?>" class="btn btn-block btn-splash-secondary <?= $settings->secondary_button_border_radius ?? 'rounded' ?>">
                                        <?php if(!$settings->secondary_button_name): ?>
                                            <i class="fas fa-fw fa-sm fa-home mr-1"></i>
                                        <?php endif ?>
                                        <?= $settings->secondary_button_name ?: l('link.splash.home') ?>
                                    </a>
                                </div>
                            <?php endif ?>

                            <div class="<?= !empty($settings->secondary_button_url) ? 'col-6' : 'col-12' ?>">
                                <a href="#" id="link_continue" class="btn btn-block btn-splash-primary <?= $settings->primary_button_border_radius ?? 'rounded' ?> disabled">
                                    <i class="fas fa-fw fa-sm fa-link mr-1"></i> <?= $primary_btn_name ?>
                                </a>
                            </div>
                        </div>
                    </form>
                <?php endif ?>

                <div class="text-muted mt-2 small" id="link_unlock_seconds">
                    <?= sprintf(l('link.splash.link_unlock_seconds'), $data->splash_page->link_unlock_seconds ?? settings()->links->splash_page_link_unlock_seconds) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    let link_unlock_seconds = <?= (int) ($data->splash_page->link_unlock_seconds ?? settings()->links->splash_page_link_unlock_seconds) ?>;
    let splash_page_auto_redirect = <?= json_encode((bool) ($data->splash_page->auto_redirect ?? settings()->links->splash_page_auto_redirect)) ?>;
    let destination_url = <?= json_encode($destination_url) ?>;
    let link_id = <?= json_encode($link_id) ?>;
    let has_details_form = <?= json_encode($has_details_form) ?>;
    let always_show = <?= json_encode((bool) ($settings->always_show ?? false)) ?>;

    let link_unlock_seconds_remaining = link_unlock_seconds;

    let get_unlocked_url = (url) => {
        let separator = url.indexOf('?') !== -1 ? '&' : '?';
        return url + separator + 'splash=passed';
    };

    let unlock_link = () => {
        document.querySelector('#link_unlock_seconds').classList.add('d-none');
        let continue_btn = document.querySelector('#link_continue');
        if(continue_btn) {
            continue_btn.classList.remove('disabled');
            continue_btn.href = get_unlocked_url(destination_url);
        }

        if(splash_page_auto_redirect && !has_details_form) {
            window.location.replace(get_unlocked_url(destination_url));
        }

        if(link_id && !always_show) {
            set_cookie('link_unlocked_' + link_id, <?= json_encode(md5($link_id . $link_id)) ?>, 1, <?= json_encode(COOKIE_PATH) ?>);
        }
    };

    if(link_unlock_seconds > 0) {
        let countdown = setInterval(() => {
            document.querySelector('#link_unlock_seconds').innerHTML = <?= json_encode(l('link.splash.link_unlock_seconds')) ?>.replace('%s', link_unlock_seconds_remaining);

            link_unlock_seconds_remaining -= 1;

            if(link_unlock_seconds_remaining < 0) {
                clearInterval(countdown);
                unlock_link();
            }
        }, 1000);
    } else {
        unlock_link();
    }

    /* Skip button handler */
    let skip_button = document.querySelector('#splash_skip_button');
    if(skip_button) {
        skip_button.addEventListener('click', event => {
            event.preventDefault();
            if(link_id && !always_show) {
                set_cookie('link_unlocked_' + link_id, <?= json_encode(md5($link_id . $link_id)) ?>, 1, <?= json_encode(COOKIE_PATH) ?>);
            }
            window.location.replace(get_unlocked_url(destination_url));
        });
    }

    /* Details form AJAX submission */
    let details_form = document.querySelector('#splash_details_form');
    if(details_form) {
        details_form.addEventListener('submit', event => {
            event.preventDefault();

            let form = $(event.currentTarget);
            let submit_btn = form.find('button[type="submit"]');
            let notification_container = form.find('.notification-container');
            notification_container.html('');
            submit_btn.attr('disabled', 'disabled').addClass('disabled');

            $.ajax({
                type: 'POST',
                url: `${site_url}l/link/splash_page_collector`,
                data: form.serialize(),
                dataType: 'json',
                success: (data) => {
                    if(data.status == 'error') {
                        submit_btn.removeAttr('disabled').removeClass('disabled');
                        notification_container.html(`<div class="alert alert-danger">${data.message}</div>`);
                    } else if(data.status == 'success') {
                        notification_container.html(`<div class="alert alert-success">${data.message}</div>`);
                        if(link_id && !always_show) {
                            set_cookie('link_unlocked_' + link_id, <?= json_encode(md5($link_id . $link_id)) ?>, 1, <?= json_encode(COOKIE_PATH) ?>);
                        }
                        setTimeout(() => {
                            window.location.replace(get_unlocked_url(data.details.destination_url || destination_url));
                        }, 800);
                    }
                },
                error: () => {
                    submit_btn.removeAttr('disabled').removeClass('disabled');
                    notification_container.html(`<div class="alert alert-danger"><?= json_encode(l('global.error_message.basic')) ?></div>`);
                }
            });
        });
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
