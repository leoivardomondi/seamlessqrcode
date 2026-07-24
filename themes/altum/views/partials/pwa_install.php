<?php defined('ALTUMCODE') || die() ?>

<?php if(\Altum\Authentication::check()): ?>
<div class="modal fade" id="standard_pwa_install_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-mobile-alt text-primary mr-2"></i>
                        Install SEAMLESS QR CODE
                    </h5>

                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted mb-4">
                    Add this app to your device for faster access and a cleaner fullscreen experience.
                </p>

                <div id="standard_pwa_native_ready" class="alert alert-success d-none">
                    Your browser is ready to install this app.
                </div>

                <button type="button" class="btn btn-block btn-primary" data-standard-pwa-install>
                    Install app
                </button>

                <button type="button" class="btn btn-block btn-link text-muted" data-standard-pwa-dismiss>
                    Maybe later
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    (() => {
        const site_url = <?= json_encode(SITE_URL) ?>;
        const modal_selector = '#standard_pwa_install_modal';
        const modal = document.querySelector(modal_selector);
        const install_button = document.querySelector('[data-standard-pwa-install]');
        const dismiss_button = document.querySelector('[data-standard-pwa-dismiss]');
        const native_ready = document.querySelector('#standard_pwa_native_ready');
        const storage_key = <?= json_encode(md5(SITE_URL) . '_standard_pwa_install_dismissed_at') ?>;
        const installed_key = <?= json_encode(md5(SITE_URL) . '_standard_pwa_install_installed') ?>;
        const pageviews_key = <?= json_encode(md5(SITE_URL) . '_standard_pwa_install_pageviews') ?>;
        const dismiss_cooldown = 7 * 24 * 60 * 60 * 1000;
        const install_delay = 1000;
        const minimum_pageviews = 1;

        let deferred_install_prompt = null;
        let native_prompt_available = false;
        let show_timer = null;
        let installed_context = localStorage.getItem(installed_key) === '1';

        if('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register(site_url + 'sw.js', { scope: site_url }).catch(() => {});
            });
        }

        if(!modal) {
            return;
        }

        const is_standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        if(is_standalone) {
            localStorage.setItem(installed_key, '1');
            return;
        }

        const dismissed_at = parseInt(localStorage.getItem(storage_key) || '0');

        if(dismissed_at && Date.now() - dismissed_at < dismiss_cooldown) {
            return;
        }

        if(typeof navigator.getInstalledRelatedApps === 'function') {
            navigator.getInstalledRelatedApps()
                .then(related_apps => {
                    if(Array.isArray(related_apps) && related_apps.length > 0) {
                        installed_context = true;
                        localStorage.setItem(installed_key, '1');

                        if(window.jQuery && typeof jQuery.fn.modal == 'function') {
                            jQuery(modal_selector).modal('hide');
                        }
                    }
                })
                .catch(() => {});
        }

        const pageviews = parseInt(sessionStorage.getItem(pageviews_key) || '0') + 1;
        sessionStorage.setItem(pageviews_key, pageviews);

        const set_install_state = has_native_prompt => {
            native_prompt_available = has_native_prompt || native_prompt_available;
            install_button && install_button.classList.remove('d-none');

            if(native_prompt_available) {
                native_ready && native_ready.classList.remove('d-none');
            } else {
                native_ready && native_ready.classList.add('d-none');
            }
        };

        const show_install_modal = has_native_prompt => {
            if(pageviews < minimum_pageviews || installed_context) {
                return;
            }

            set_install_state(has_native_prompt);

            if(show_timer) {
                clearTimeout(show_timer);
            }

            show_timer = setTimeout(() => {
                if(installed_context) {
                    return;
                }

                if(window.jQuery && typeof jQuery.fn.modal == 'function') {
                    jQuery(modal_selector).modal('show');
                }
            }, install_delay);
        };

        window.addEventListener('beforeinstallprompt', event => {
            event.preventDefault();
            deferred_install_prompt = event;
            show_install_modal(true);
        });

        install_button && install_button.addEventListener('click', async () => {
            if(!deferred_install_prompt) {
                if(window.jQuery && typeof jQuery.fn.modal == 'function') {
                    jQuery(modal_selector).modal('hide');
                }
                return;
            }

            try {
                deferred_install_prompt.prompt();
                await deferred_install_prompt.userChoice;
            } catch (error) {
                // Ignore prompt errors and close the helper.
            }

            deferred_install_prompt = null;
            native_prompt_available = false;

            if(window.jQuery && typeof jQuery.fn.modal == 'function') {
                jQuery(modal_selector).modal('hide');
            }
        });

        dismiss_button && dismiss_button.addEventListener('click', () => {
            localStorage.setItem(storage_key, String(Date.now()));

            if(window.jQuery && typeof jQuery.fn.modal == 'function') {
                jQuery(modal_selector).modal('hide');
            }
        });

        window.addEventListener('appinstalled', () => {
            installed_context = true;
            localStorage.setItem(installed_key, '1');
            localStorage.removeItem(storage_key);

            if(window.jQuery && typeof jQuery.fn.modal == 'function') {
                jQuery(modal_selector).modal('hide');
            }
        });
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'standard_pwa_install') ?>
<?php endif ?>
