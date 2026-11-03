<?php defined('ALTUMCODE') || die(); ?>

<?php $welcome_extra = json_decode(user()->extra ?? '') ?: (object) []; ?>

<?php if(is_logged_in()): ?>
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
                                    maxlength="20"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    required="required"
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

                const clean_phone = value => {
                    if(!value) return '';
                    let digits = value.replace(/\D+/g, '');
                    if(digits.startsWith('254') && digits.length >= 12) {
                        digits = '0' + digits.slice(3, 12);
                    } else if((digits.startsWith('7') || digits.startsWith('1')) && digits.length === 9) {
                        digits = '0' + digits;
                    } else if(digits.startsWith('0') && digits.length > 10) {
                        digits = digits.slice(0, 10);
                    }
                    return digits;
                };

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

                const validate_phone = () => phone_regex.test(clean_phone(phone_input.value));

                const refresh_state = () => {
                    const is_valid = validate_phone();
                    update_button_state(is_valid);

                    if(phone_input.value.trim().length > 3) {
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

                phone_input.addEventListener('blur', () => {
                    let cleaned = clean_phone(phone_input.value);
                    if(phone_regex.test(cleaned)) {
                        phone_input.value = cleaned;
                    }
                });

                form.addEventListener('submit', async event => {
                    event.preventDefault();

                    set_feedback('');
                    let cleaned = clean_phone(phone_input.value);
                    if(phone_regex.test(cleaned)) {
                        phone_input.value = cleaned;
                    }

                    if(!validate_phone()) {
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
                    jQuery(modal_selector).on('shown.bs.modal', () => {
                        phone_input.focus();
                    });
                    jQuery(modal_selector).modal('show');
                }

                setTimeout(() => {
                    phone_input.focus();
                }, 300);

                refresh_state();
            })();
        </script>
        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'welcome_phone_capture') ?>
    <?php endif ?>

    <?php if(isset($_GET['welcome']) || isset($_GET['welcome_setup'])): ?>
    <?php if(!((user()->country ?? null) == 'KE' && empty(user()->billing->phone)) && (empty(user()->billing->phone) || empty($welcome_extra->signup_industry))): ?>
    <div class="modal fade" id="welcome_setup_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="welcome_setup_modal_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4 p-lg-5">
                    <div class="mb-3 text-primary"><i class="fas fa-fw fa-wand-magic-sparkles fa-2x"></i></div>
                    <h5 class="mb-2" id="welcome_setup_modal_title">Let’s personalize your account</h5>
                    <p class="text-muted mb-4">Tell us what you are building and we’ll recommend the right package, prepare your account, and help you start faster. It only takes a few quick answers.</p>
                    <form action="<?= url('onboarding-profile') ?>" method="post" data-profile-form>
                        <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />
                        <div class="form-group">
                            <label for="welcome_profile_phone">Phone number</label>
                            <input id="welcome_profile_phone" type="tel" name="phone" class="form-control" value="<?= e(user()->billing->phone ?? '') ?>" maxlength="32" inputmode="tel" autocomplete="tel" placeholder="+254 7XX XXX XXX" required="required" />
                        </div>
                        <div class="form-group">
                            <label for="welcome_profile_industry">Industry</label>
                            <select id="welcome_profile_industry" name="industry" class="custom-select" required="required">
                                <option value="">Select your industry</option>
                                <option value="restaurant" <?= ($welcome_extra->signup_industry ?? '') == 'restaurant' ? 'selected="selected"' : null ?>>Restaurant / Cafe</option>
                                <option value="hotel" <?= ($welcome_extra->signup_industry ?? '') == 'hotel' ? 'selected="selected"' : null ?>>Hotel with outlets</option>
                                <option value="lounge" <?= ($welcome_extra->signup_industry ?? '') == 'lounge' ? 'selected="selected"' : null ?>>Lounge / Bar / Club</option>
                                <option value="catering" <?= ($welcome_extra->signup_industry ?? '') == 'catering' ? 'selected="selected"' : null ?>>Catering / Food truck</option>
                                <option value="corporate" <?= ($welcome_extra->signup_industry ?? '') == 'corporate' ? 'selected="selected"' : null ?>>Corporate / Business cards</option>
                                <option value="retail" <?= ($welcome_extra->signup_industry ?? '') == 'retail' ? 'selected="selected"' : null ?>>Retail / Shop</option>
                                <option value="events" <?= ($welcome_extra->signup_industry ?? '') == 'events' ? 'selected="selected"' : null ?>>Events</option>
                                <option value="other" <?= ($welcome_extra->signup_industry ?? '') == 'other' ? 'selected="selected"' : null ?>>Other business</option>
                            </select>
                        </div>
                        <div class="alert alert-danger d-none" data-profile-feedback></div>
                        <button type="submit" class="btn btn-primary btn-block rounded-2x" data-profile-submit>Save and continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php ob_start() ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const setupModal = document.querySelector('#welcome_setup_modal');
            if(!setupModal || !window.jQuery || typeof jQuery.fn.modal !== 'function') return;

            const profileForm = setupModal.querySelector('[data-profile-form]');
            const profileFeedback = setupModal.querySelector('[data-profile-feedback]');
            const profileSubmit = setupModal.querySelector('[data-profile-submit]');
            const originalProfileSubmit = profileSubmit ? profileSubmit.innerHTML : '';

            profileForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                profileFeedback.textContent = '';
                profileFeedback.classList.add('d-none');
                profileSubmit.disabled = true;
                profileSubmit.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Saving...';

                try {
                    const response = await fetch(profileForm.action, { method: 'post', credentials: 'same-origin', headers: { 'Accept': 'application/json' }, body: new FormData(profileForm) });
                    const data = await response.json();
                    if(!response.ok || data.status !== 'success') throw new Error(Array.isArray(data.message) ? (data.message[0] || 'Please check your details.') : (data.message || 'Please check your details.'));
                    window.location.href = data.details.redirect_url;
                } catch(error) {
                    profileFeedback.textContent = error.message || 'Please check your details.';
                    profileFeedback.classList.remove('d-none');
                    profileSubmit.disabled = false;
                    profileSubmit.innerHTML = originalProfileSubmit;
                }
            });

            window.setTimeout(function () {
                jQuery('#welcome_setup_modal').modal('show');
            }, 500);
        });
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'welcome_setup_prompt') ?>
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
<?php endif ?>
