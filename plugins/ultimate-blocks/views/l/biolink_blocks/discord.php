<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <div class="link-iframe-round">
        <iframe class="embed-responsive-item" scrolling="no" frameborder="no" style="height: 300px;width:100%;overflow:hidden;background:transparent;" src="<?= 'https://discord.com/widget?id=' . $data->link->settings->server_id . '&theme=dark' ?>"></iframe>
    </div>
</div>
