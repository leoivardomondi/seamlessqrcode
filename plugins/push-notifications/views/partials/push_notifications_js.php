<?php defined('ALTUMCODE') || die() ?>

<?php if(\Altum\Authentication::check() || (!\Altum\Authentication::check() && settings()->push_notifications->guests_is_enabled)): ?>
<?php ob_start() ?>
<div class="modal fade" id="push_notifications_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-bolt-lightning text-dark mr-2"></i>
                        <?= l('push_notifications_modal.header') ?>
                    </h5>

                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="notification-container"></div>

                <p class="text-muted"><?= l('push_notifications_modal.subheader') ?></p>

                <div class="mt-4">
                    <div id="push_notifications_modal_incompatible" class="alert alert-info"><?= l('push_notifications_modal.incompatible') ?></div>
                    <button type="button" id="push_notifications_modal_subscribe" class="btn btn-block btn-primary d-none"><?= l('push_notifications_modal.subscribe') ?></button>
                    <button type="button" id="push_notifications_modal_unsubscribe" class="btn btn-block btn-danger d-none"><?= l('push_notifications_modal.unsubscribe') ?></button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    if('serviceWorker' in navigator) {
        let push_notifications_public_key = <?= json_encode(settings()->push_notifications->public_key) ?>;
        navigator.serviceWorker.register(<?= json_encode(SITE_URL . 'sw.js') ?>, {
            scope: <?= json_encode(SITE_URL) ?>,
        });

        /* Get the current status of the web push subscription */
        let process_subscription_status = () => {
            navigator.serviceWorker.ready.then(sw => {
                sw.pushManager.getSubscription()
                    .then(subscription => {
                        /* No subscription */
                        if (!subscription) {
                            document.querySelector('#push_notifications_modal_subscribe').classList.remove('d-none');
                            document.querySelector('#push_notifications_modal_unsubscribe').classList.add('d-none');
                        }

                        /* Subscribed */
                        else {
                            document.querySelector('#push_notifications_modal_subscribe').classList.add('d-none');
                            document.querySelector('#push_notifications_modal_unsubscribe').classList.remove('d-none');
                        }

                        document.querySelector('#push_notifications_modal_incompatible').classList.add('d-none');
                    });
            });
        }

        let unsubscribe = () => {
            pause_submit_button(document.querySelector('#push_notifications_modal_unsubscribe'));

            navigator.serviceWorker.ready.then(sw => {
                sw.pushManager.getSubscription().then(subscription => {
                    subscription.unsubscribe().then(event => {
                        subscription = subscription.toJSON();

                        /* Prepare form data */
                        let form = new FormData();
                        form.set('endpoint', subscription.endpoint);
                        form.set('auth', subscription.keys.auth);
                        form.set('p256dh', subscription.keys.p256dh);

                        /* Send request to server */
                        let response = fetch(`${url}push-subscribers/delete_ajax`, {
                            method: 'post',
                            body: form
                        })

                        enable_submit_button(document.querySelector('#push_notifications_modal_unsubscribe'));
                        process_subscription_status();
                    });
                })
            });
        }

        let request_push_notification_permission_and_subscribe = event => {
            event.preventDefault();

            Notification.requestPermission().then(permission => {

                if(permission === 'granted') {
                    navigator.serviceWorker.ready.then(sw => {

                        let subscribe = () => {
                            pause_submit_button(document.querySelector('#push_notifications_modal_subscribe'));

                            sw.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: push_notifications_public_key
                            }).then(subscription => {
                                subscription = subscription.toJSON();

                                /* Prepare form data */
                                let form = new FormData();
                                form.set('endpoint', subscription.endpoint);
                                form.set('auth', subscription.keys.auth);
                                form.set('p256dh', subscription.keys.p256dh);

                                /* Send request to server */
                                let response = fetch(`${url}push-subscribers/create_ajax`, {
                                    method: 'post',
                                    body: form
                                });

                                enable_submit_button(document.querySelector('#push_notifications_modal_subscribe'));
                                process_subscription_status();
                            });
                        }

                        /* Get current subscription */
                        sw.pushManager.getSubscription()
                            .then(subscription => {
                                /* No subscription, try to subscribe */
                                if (!subscription) {
                                    subscribe();
                                }

                                /* Subscribed */
                                else {
                                    unsubscribe();
                                    subscribe();
                                }
                            });

                    });
                }

                if(permission == 'denied') {
                    alert(<?= json_encode(l('push_notifications_modal.denied')) ?>);
                }

            });
        }

        /* On subscribe click */
        document.querySelector('#push_notifications_modal_subscribe').addEventListener('click', request_push_notification_permission_and_subscribe);

        /* On unsubscribe click */
        document.querySelector('#push_notifications_modal_unsubscribe').addEventListener('click', unsubscribe);

        /* On modal show */
        $('#push_notifications_modal').on('show.bs.modal', event => {
            process_subscription_status();
        });

        <?php if(settings()->push_notifications->ask_to_subscribe_is_enabled): ?>
        if(!localStorage.getItem('push_notifications_modal_has_been_shown')) {
            setTimeout(() => {
                $('#push_notifications_modal').modal('show');

                localStorage.setItem('push_notifications_modal_has_been_shown', 1);
            }, <?= (int) (settings()->push_notifications->ask_to_subscribe_delay ?? 2) ?> * 1000);
        }
        <?php endif ?>
    } else {
        /* ;) */
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'push-notifications') ?>
<?php endif ?>
