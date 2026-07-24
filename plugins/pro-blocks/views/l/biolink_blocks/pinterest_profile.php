<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2 d-flex justify-content-center">
    <a data-pin-do="embedUser" data-pin-board-width="auto" data-pin-scale-height="280" data-pin-scale-width="80" href="<?= $data->link->location_url ?>"></a>
</div>

<?php if(!\Altum\Event::exists_content_type_key('javascript', 'pinterest_profile')): ?>
    <?php ob_start() ?>
    <script async defer src="//assets.pinterest.com/js/pinit.js"></script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'pinterest_profile') ?>
<?php endif ?>
