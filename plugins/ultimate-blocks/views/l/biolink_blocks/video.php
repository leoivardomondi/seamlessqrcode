<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <video class="w-100 link-round" title="<?= $data->link->settings->name ?>" poster="<?= $data->link->settings->poster_url ?? null ?>" controls>
        <source src="<?= UPLOADS_FULL_URL . 'files/' . $data->link->settings->file ?>">
    </video>
</div>
