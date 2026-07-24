<?php defined('ALTUMCODE') || die() ?>

<?php if(\Altum\Authentication::check()): ?>
<div class="modal fade" id="pwa_install_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-mobile-alt text-primary mr-2"></i>
                        Install <?= settings()->pwa->app_name ?: settings()->main->title ?>
                    </h5>

                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted mb-4">
                    Add this app to your device for faster access and a cleaner fullscreen experience.
                </p>

                <div id="pwa_ios_install_help" class="alert alert-info d-none">
                    Open the browser share menu, then choose <strong>Add to Home Screen</strong>.
                </div>

                <div id="pwa_browser_install_help" class="alert alert-info d-none">
                    Use your browser menu and choose <strong>Install app</strong> or <strong>Add to Home screen</strong>.
                </div>

                <button type="button" class="btn btn-block btn-primary" data-pwa-install>
                    Install app
                </button>

                <button type="button" class="btn btn-block btn-link text-muted" data-pwa-dismiss>
                    Maybe later
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    if('serviceWorker' in navigator) {
        navigator.serviceWorker.register(<?= json_encode(SITE_URL . 'sw.js') ?>, {
            scope: <?= json_encode(SITE_URL) ?>
        }).catch(() => {});
    }

    (() => {
        let install_prompt = null;
        let modal = document.querySelector('#pwa_install_modal');
        let install_button = document.querySelector('[data-pwa-install]');
        let dismiss_button = document.querySelector('[data-pwa-dismiss]');
        let ios_help = document.querySelector('#pwa_ios_install_help');
        let browser_help = document.querySelector('#pwa_browser_install_help');
        let storage_key = <?= json_encode(md5(SITE_URL) . '_pwa_install_modal_v2_dismissed_at') ?>;
        let pageviews_key = <?= json_encode(md5(SITE_URL) . '_pwa_install_modal_pageviews') ?>;
        let minimum_pageviews = <?= (int) (settings()->pwa->display_install_bar_minimum_pageviews_count ?? 0) ?>;
        let delay = <?= (int) (settings()->pwa->display_install_bar_delay ?? 0) ?> * 1000;
        let dismiss_cooldown = 7 * 24 * 60 * 60 * 1000;
        let native_install_event_received = false;
        let show_timer = null;

        let is_standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        let dismissed_at = parseInt(localStorage.getItem(storage_key) || '0');
        let is_ios = /iphone|ipad|ipod/i.test(window.navigator.userAgent);

        if(!modal || is_standalone || (dismissed_at && Date.now() - dismissed_at < dismiss_cooldown)) {
            return;
        }

        let pageviews = parseInt(sessionStorage.getItem(pageviews_key) || '0') + 1;
        sessionStorage.setItem(pageviews_key, pageviews);

        let show_modal = native_install_available => {
            if(pageviews < minimum_pageviews) {
                return;
            }

            if(native_install_available) {
                native_install_event_received = true;
            }

            if(is_ios) {
                install_button && install_button.classList.remove('d-none');
                ios_help && ios_help.classList.remove('d-none');
                browser_help && browser_help.classList.add('d-none');
            } else if(native_install_available) {
                install_button && install_button.classList.remove('d-none');
                ios_help && ios_help.classList.add('d-none');
                browser_help && browser_help.classList.add('d-none');
            } else {
                install_button && install_button.classList.remove('d-none');
                ios_help && ios_help.classList.add('d-none');
                browser_help && browser_help.classList.remove('d-none');
            }

            if(show_timer) {
                clearTimeout(show_timer);
            }

            show_timer = setTimeout(() => $('#pwa_install_modal').modal('show'), delay);
        };

        window.addEventListener('beforeinstallprompt', event => {
            event.preventDefault();
            install_prompt = event;
            show_modal(true);
        });

        install_button && install_button.addEventListener('click', async () => {
            if(!install_prompt) {
                if(is_ios) {
                    ios_help && ios_help.classList.remove('d-none');
                    browser_help && browser_help.classList.add('d-none');
                } else {
                    ios_help && ios_help.classList.add('d-none');
                    browser_help && browser_help.classList.remove('d-none');
                }

                return;
            }

            install_prompt.prompt();
            await install_prompt.userChoice;
            install_prompt = null;
            $('#pwa_install_modal').modal('hide');
        });

        dismiss_button && dismiss_button.addEventListener('click', () => {
            localStorage.setItem(storage_key, String(Date.now()));
            $('#pwa_install_modal').modal('hide');
        });

        window.addEventListener('appinstalled', () => {
            localStorage.removeItem(storage_key);
            $('#pwa_install_modal').modal('hide');
        });

        setTimeout(() => {
            if(!native_install_event_received) {
                show_modal(false);
            }
        }, 1500);
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'pwa') ?>
<?php endif ?>
