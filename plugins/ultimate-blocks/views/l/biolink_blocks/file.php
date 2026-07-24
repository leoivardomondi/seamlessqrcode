<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <a href="<?= UPLOADS_FULL_URL . 'files/' . $data->link->settings->file ?>" data-track-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="btn btn-block btn-primary link-btn link-hover-animation <?= 'link-btn-' . $data->link->settings->border_radius ?> <?= $data->link->design->link_class ?>" style="<?= $data->link->design->link_style ?>" download data-text-color data-border-width data-border-radius data-border-style data-border-color data-border-shadow data-animation data-background-color data-text-alignment>
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

