<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <div class="link-iframe-round">
        <?= $data->link->settings->content ?>
    </div>
</div>

<style>
.embedly-card-hug {
    max-width: 100% !important;
}
.embedly-card-hug iframe {
    width: 100% !important;
}
</style>
