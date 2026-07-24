<?php defined('ALTUMCODE') || die() ?>

<?php if(count((array) $data->link->settings->items)): ?>
    <div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
        <section class="splide <?= 'splide_' . $data->link->biolink_block_id ?>">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php foreach((array) $data->link->settings->items as $key => $item): ?>
                        <li class="splide__slide">
                            <?php if($item->location_url): ?>
                                <a href="<?= $item->location_url . $data->link->utm_query ?>" data-track-biolink-block-id="<?= $data->link->biolink_block_id ?>" target="<?= $data->link->settings->open_in_new_tab ? '_blank' : '_self' ?>" class="link-hover-animation">
                                    <img src="<?= UPLOADS_FULL_URL . 'block_images/' . $item->image ?>" class="link-image-slider-image rounded link-hover-animation" style="<?= 'width: ' . $data->link->settings->width_height . 'rem;' . 'height: ' . $data->link->settings->width_height . 'rem;' ?>" alt="<?= $item->image_alt ?>" loading="lazy" />
                                </a>
                            <?php else: ?>
                                <img src="<?= UPLOADS_FULL_URL . 'block_images/' . $item->image ?>" class="link-image-slider-image rounded link-hover-animation" style="<?= 'width: ' . $data->link->settings->width_height . 'rem;' . 'height: ' . $data->link->settings->width_height . 'rem;' ?>" alt="<?= $item->image_alt ?>" loading="lazy" />
                            <?php endif ?>
                    <?php endforeach ?>
                </ul>
            </div>
        </section>
    </div>

    <?php if(!\Altum\Event::exists_content_type_key('javascript', 'image_slider')): ?>
        <?php ob_start() ?>
        <link href="<?= ASSETS_FULL_URL . 'css/libraries/splide.min.css' ?>" rel="stylesheet" media="screen,print">
        <?php \Altum\Event::add_content(ob_get_clean(), 'head', 'image_slider') ?>

        <?php ob_start() ?>
        <script src="<?= ASSETS_FULL_URL . 'js/libraries/splide.min.js' ?>"></script>
        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'image_slider') ?>
    <?php endif ?>

    <?php ob_start() ?>
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', () => {
            let splide = new Splide('.<?= 'splide_' . $data->link->biolink_block_id ?>', {
                type: 'loop',
                arrows: <?= json_encode($data->link->settings->display_arrows) ?>,
                autoplay: <?= json_encode($data->link->settings->autoplay) ?>,
                pagination: <?= json_encode($data->link->settings->display_pagination) ?>,
                autoWidth: <?= json_encode($data->link->settings->display_multiple) ?>,
                gap: '<?= $data->link->settings->gap . 'rem' ?>',
            });
            splide.mount();
        });
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
<?php endif ?>
