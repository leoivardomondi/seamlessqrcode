<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <div class="d-flex align-items-center justify-content-center">
        <div id="<?= 'flipdown_' . $data->link->biolink_block_id ?>" class="flipdown" data-end-date="<?= (new DateTime($data->link->settings->counter_end_date))->getTimestamp() ?>"></div>
    </div>
</div>

<?php if(!\Altum\Event::exists_content_type_key('javascript', 'countdown')): ?>
    <?php ob_start() ?>
    <link href="<?= ASSETS_FULL_URL . 'css/libraries/flipdown.min.css' ?>" rel="stylesheet" media="screen,print">
    <?php \Altum\Event::add_content(ob_get_clean(), 'head', 'countdown') ?>

    <?php ob_start() ?>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/flipdown.min.js' ?>"></script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'countdown') ?>
<?php endif ?>

<?php ob_start() ?>
<script>
    'use strict';
    document.addEventListener('DOMContentLoaded', () => {
        new FlipDown(
            parseInt(document.querySelector('#<?= 'flipdown_' . $data->link->biolink_block_id ?>').getAttribute('data-end-date')),
            '<?= 'flipdown_' . $data->link->biolink_block_id ?>',
            {
                theme: <?= json_encode($data->link->settings->theme) ?>,
                headings: [<?= json_encode(l('global.date.days')) ?>, <?= json_encode(l('global.date.hours')) ?>, <?= json_encode(l('global.date.minutes')) ?>, <?= json_encode(l('global.date.seconds')) ?>]
            }
        ).start().ifEnded(() => {
            /* :) */
        });
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
