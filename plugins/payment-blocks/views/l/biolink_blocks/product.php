<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <a href="#" data-toggle="modal" data-target="<?= '#product_' . $data->link->biolink_block_id ?>" class="btn btn-block btn-primary link-btn link-hover-animation <?= 'link-btn-' . $data->link->settings->border_radius ?> <?= $data->link->design->link_class ?>" style="<?= $data->link->design->link_style ?>" data-text-color data-border-width data-border-radius data-border-style data-border-color data-border-shadow data-animation data-background-color data-text-alignment>
        <div class="link-btn-image-wrapper <?= 'link-btn-' . $data->link->settings->border_radius ?>" <?= $data->link->settings->image ? null : 'style="display: none;"' ?>>
            <img src="<?= $data->link->settings->image ? UPLOADS_FULL_URL . 'block_thumbnail_images/' . $data->link->settings->image : null ?>" class="link-btn-image" loading="lazy" />
        </div>

        <span data-icon>
            <?php if($data->link->settings->icon): ?>
                <i class="<?= $data->link->settings->icon ?> mr-1"></i>
            <?php endif ?>
        </span>

        <span data-name><?= $data->link->settings->name ?></span>
    </a>
</div>

<?php ob_start() ?>
<div class="modal fade" id="<?= 'product_' . $data->link->biolink_block_id ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $data->link->settings->name ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= l('global.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="<?= 'product_form_' . $data->link->biolink_block_id ?>" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="biolink_block_id" value="<?= $data->link->biolink_block_id ?>" />

                    <div class="notification-container"></div>

                    <p><?= $data->link->settings->description ?></p>

                    <div class="form-group">
                        <label for="<?= 'product_form_price_' . $data->link->biolink_block_id ?>"><?= l('biolink_product_modal.price') ?></label>
                        <div class="input-group">
                            <input id="<?= 'product_form_price_' . $data->link->biolink_block_id ?>" type="number" min="<?= $data->link->settings->minimum_price ?? 0 ?>" class="form-control form-control-lg" name="price" value="<?= $data->link->settings->price ?>" <?= $data->link->settings->allow_custom_price ? null : 'readonly="readonly"' ?> required="required" />
                            <div class="input-group-append">
                                <span class="input-group-text"><?= $data->link->settings->currency ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="<?= 'product_form_email_' . $data->link->biolink_block_id ?>"><?= l('global.email') ?></label>
                        <input type="email" name="email" id="<?= 'product_form_email_' . $data->link->biolink_block_id ?>" class="form-control form-control-lg" maxlength="320" required="required" />
                        <small class="form-text text-muted"><?= l('biolink_product_modal.email_help') ?></small>
                    </div>

                    <div class="row">
                        <?php foreach($data->link->settings->payment_processors_ids as $payment_processor_id): ?>
                            <label class="col-6 my-2 custom-radio-box">
                                <input type="radio" name="payment_processor_id" value="<?= $payment_processor_id ?>" data-processor="" class="custom-control-input" required="required">

                                <div class="card">
                                    <div class="card-body d-flex align-items-center justify-content-center">
                                        <i class="<?= $data->payment_processors[$payment_processor_id]->icon ?> fa-fw mr-2" style="color: <?= $data->payment_processors[$payment_processor_id]->color ?>"></i>
                                        <span class="font-weight-bold"><?= l('pay.custom_plan.' . $data->payment_processors[$payment_processor_id]->processor) ?></span>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach ?>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-lg btn-primary" data-is-ajax><?= l('biolink_product_modal.submit') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="<?= 'product_thank_you_' . $data->link->biolink_block_id ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $data->link->settings->thank_you_title ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= l('global.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p><?= $data->link->settings->thank_you_description ?></p>
            </div>

        </div>
    </div>
</div>
<?php \Altum\Event::add_content(ob_get_clean(), 'modals') ?>


<?php if(!\Altum\Event::exists_content_type_key('javascript', 'product')): ?>
    <?php ob_start() ?>
    <script>
        'use strict';

        /* Form handling */
        $('form[id^="product_"]').on('submit', event => {
            let notification_container = event.currentTarget.querySelector('.notification-container');
            notification_container.innerHTML = '';
            pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

            $.ajax({
                type: 'POST',
                url: `${site_url}l/link/payment_generator`,
                data: $(event.currentTarget).serialize(),
                dataType: 'json',
                success: (data) => {
                    enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                    if (data.status == 'error') {
                        display_notifications(data.message, 'error', notification_container);
                    } else if (data.status == 'success') {
                        window.location.replace(data.details.checkout_url);
                    }

                },
                error: () => {
                    enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));
                    display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
                },
            });

            event.preventDefault();
        })

        /* Thank you modal */
        if(window.location.search) {
            let url_params = new URLSearchParams(window.location.search);

            if(url_params && url_params.get('payment_thank_you') && url_params.get('payment_thank_you') == 'product') {
                let biolink_block_id = url_params.get('biolink_block_id');
                $(`#product_thank_you_${biolink_block_id}`).modal('show');
            }
        }
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'product') ?>
<?php endif ?>


