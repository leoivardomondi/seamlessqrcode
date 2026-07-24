<?php defined('ALTUMCODE') || die(); ?>

<?php if(is_logged_in() && isset($_GET['welcome'])): ?>
    <?php if((user()->country ?? null) == 'KE' && empty(user()->billing->phone)): ?>
        <div class="modal fade" id="welcome_phone_capture_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-body p-4 p-lg-5">
                        <div class="mb-4">
                            <h5 class="modal-title mb-2">
                                <?= l('welcome_phone_capture.title') ?>
                            </h5>

                            <p class="text-muted mb-0">
                                <?= l('welcome_phone_capture.subtitle') ?>
                            </p>
                        </div>

                        <div class="alert alert-danger d-none" data-phone-feedback></div>

                        <form action="<?= url('phone-capture') ?>" method="post" novalidate>
                            <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

                            <div class="form-group">
                                <label for="welcome_phone_number"><?= l('welcome_phone_capture.label') ?></label>
                                <input
                                    id="welcome_phone_number"
                                    type="tel"
                                    name="phone_number"
                                    class="form-control"
                                    value=""
                                    maxlength="10"
                                    inputmode="numeric"
                                    autocomplete="tel"
                                    required="required"
                                    pattern="^(07|01)[0-9]{8}$"
                                    placeholder="<?= l('welcome_phone_capture.placeholder') ?>"
                                    data-phone-input
                                    autofocus="autofocus"
                                />
                            </div>

                            <button type="submit" class="btn btn-primary btn-block" data-phone-submit>
                                <?= l('link.splash.continue') ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php ob_start() ?>
        <script>
            'use strict';

            (() => {
                const modal_selector = '#welcome_phone_capture_modal';
                const modal = document.querySelector(modal_selector);

                if(!modal) {
                    return;
                }

                const form = modal.querySelector('form');
                const phone_input = modal.querySelector('[data-phone-input]');
                const submit_button = modal.querySelector('[data-phone-submit]');
                const feedback = modal.querySelector('[data-phone-feedback]');
                const invalid_message = <?= json_encode(l('welcome_phone_capture.error')) ?>;
                const redirect_fallback = <?= json_encode(url('dashboard')) ?>;
                const loading_html = <?= json_encode('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>' . l('global.loading')) ?>;
                const original_button_html = submit_button ? submit_button.innerHTML : '';

                const normalize_phone = value => value.replace(/\D+/g, '').slice(0, 10);
                const phone_regex = /^(07|01)\d{8}$/;

                const set_feedback = message => {
                    if(!feedback) {
                        return;
                    }

                    feedback.textContent = message || '';
                    feedback.classList.toggle('d-none', !message);
                };

                const update_button_state = is_valid => {
                    if(submit_button) {
                        submit_button.disabled = !is_valid;
                    }
                };

                const validate_phone = () => phone_regex.test(phone_input.value);

                const refresh_state = () => {
                    phone_input.value = normalize_phone(phone_input.value);

                    const is_valid = validate_phone();
                    update_button_state(is_valid);

                    if(phone_input.value.length) {
                        phone_input.classList.toggle('is-invalid', !is_valid);
                    } else {
                        phone_input.classList.remove('is-invalid');
                    }

                    if(is_valid) {
                        set_feedback('');
                    }

                    return is_valid;
                };

                phone_input.addEventListener('input', () => {
                    set_feedback('');
                    refresh_state();
                });

                form.addEventListener('submit', async event => {
                    event.preventDefault();

                    set_feedback('');
                    phone_input.value = normalize_phone(phone_input.value);

                    if(!refresh_state()) {
                        phone_input.classList.add('is-invalid');
                        set_feedback(invalid_message);
                        return;
                    }

                    if(submit_button) {
                        submit_button.disabled = true;
                        submit_button.innerHTML = loading_html;
                    }

                    try {
                        const form_data = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'post',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: form_data,
                        });

                        let data = null;
                        try {
                            data = await response.json();
                        } catch (error) {
                            data = null;
                        }

                        if(!response.ok || !data) {
                            throw new Error(invalid_message);
                        }

                        if(data.status !== 'success') {
                            throw new Error(Array.isArray(data.message) ? (data.message[0] || invalid_message) : (data.message || invalid_message));
                        }

                        const redirect_url = data.details && data.details.redirect_url ? data.details.redirect_url : redirect_fallback;

                        if(window.jQuery && typeof jQuery.fn.modal === 'function') {
                            jQuery(modal_selector).modal('hide');
                        }

                        window.location.href = redirect_url;
                    } catch (error) {
                        set_feedback(error.message || invalid_message);
                        phone_input.classList.add('is-invalid');

                        if(submit_button) {
                            submit_button.disabled = false;
                            submit_button.innerHTML = original_button_html;
                        }
                    }
                });

                if(window.jQuery && typeof jQuery.fn.modal === 'function') {
                    jQuery(modal_selector).modal('show');
                }

                refresh_state();
            })();
        </script>
        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'welcome_phone_capture') ?>
    <?php endif ?>

    <?php ob_start() ?>
    <script src="<?= ASSETS_FULL_URL ?>js/libraries/tsparticles.confetti.bundle.min.js?v=<?= PRODUCT_CODE ?>"></script>

    <script>
        'use strict';

        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 },
        });
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

    <?php if(!empty(settings()->custom->welcome_js)): ?>
        <?= get_settings_custom_head_js('welcome_js') ?>
    <?php endif ?>
<?php endif ?>
