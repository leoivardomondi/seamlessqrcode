<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'pwa_install_bar_' . $data->id ?>" class="fixed-bottom d-none" style="z-index: 1050;">
    <div class="container mb-3">
        <div class="alert alert-light border shadow d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-0">
            <div class="d-flex align-items-center mb-3 mb-md-0">
                <i class="fas fa-fw fa-mobile-alt text-primary mr-3"></i>
                <div>
                    <strong><?= settings()->pwa->app_name ?: settings()->main->title ?></strong>
                    <div class="small text-muted">Install this page as an app on your device.</div>
                    <div class="small text-muted d-none" data-pwa-help="<?= $data->id ?>">Use your browser menu and choose Install app or Add to Home screen.</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-link text-muted mr-2" data-pwa-dismiss="<?= $data->id ?>"><?= l('global.close') ?></button>
                <button type="button" class="btn btn-sm btn-primary" data-pwa-install="<?= $data->id ?>">Install</button>
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
        let id = <?= json_encode($data->id) ?>;
        let install_prompt = null;
        let wrapper = document.querySelector(`#pwa_install_bar_${id}`);
        let install_button = document.querySelector(`[data-pwa-install="${id}"]`);
        let dismiss_button = document.querySelector(`[data-pwa-dismiss="${id}"]`);
        let help = document.querySelector(`[data-pwa-help="${id}"]`);
        let storage_key = `${id}_pwa_install_bar_v2_dismissed`;
        let delay = <?= (int) ($data->display_delay ?? 3) ?> * 1000;
        let native_install_event_received = false;
        let show_timer = null;

        if(!wrapper || localStorage.getItem(storage_key)) {
            return;
        }

        let show_wrapper = native_install_available => {
            if(native_install_available) {
                native_install_event_received = true;
                install_button && install_button.classList.remove('d-none');
                help && help.classList.add('d-none');
            } else {
                install_button && install_button.classList.add('d-none');
                help && help.classList.remove('d-none');
            }

            if(show_timer) {
                clearTimeout(show_timer);
            }

            show_timer = setTimeout(() => wrapper.classList.remove('d-none'), delay);
        };

        window.addEventListener('beforeinstallprompt', event => {
            event.preventDefault();
            install_prompt = event;
            show_wrapper(true);
        });

        install_button && install_button.addEventListener('click', async () => {
            if(!install_prompt) {
                return;
            }

            install_prompt.prompt();
            await install_prompt.userChoice;
            install_prompt = null;
            wrapper.classList.add('d-none');
        });

        dismiss_button && dismiss_button.addEventListener('click', () => {
            localStorage.setItem(storage_key, '1');
            wrapper.classList.add('d-none');
        });

        setTimeout(() => {
            if(!native_install_event_received) {
                show_wrapper(false);
            }
        }, 1500);
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'pwa-custom-' . $data->id) ?>
