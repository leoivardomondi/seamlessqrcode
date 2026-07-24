<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <audio class="w-100" title="<?= $data->link->settings->name ?>" controls>
        <source src="<?= UPLOADS_FULL_URL . 'files/' . $data->link->settings->file ?>">
    </audio>
</div>
