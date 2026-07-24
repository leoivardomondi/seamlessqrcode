<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <a href="#" data-toggle="modal" data-target="<?= '#pdf_document_' . $data->link->biolink_block_id ?>" data-track-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="btn btn-block btn-primary link-btn link-hover-animation <?= 'link-btn-' . $data->link->settings->border_radius ?> <?= $data->link->design->link_class ?>" style="<?= $data->link->design->link_style ?>" download data-text-color data-border-width data-border-radius data-border-style data-border-color data-border-shadow data-animation data-background-color data-text-alignment>
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
    <div class="modal fade" id="<?= 'pdf_document_' . $data->link->biolink_block_id ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><?= $data->link->settings->name ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <embed src="<?= UPLOADS_FULL_URL . 'files/' . $data->link->settings->file ?>" class="w-100 rounded" style="height: 500px;" />

                    <a href="<?= UPLOADS_FULL_URL . 'files/' . $data->link->settings->file ?>" data-track-biolink-block-id="<?= $data->link->biolink_block_id ?>" target="<?= $data->link->settings->open_in_new_tab ? '_blank' : '_self' ?>" rel="<?= $data->user->plan_settings->dofollow_is_enabled ? 'dofollow' : 'nofollow' ?>" class="mt-4 btn btn-block btn-primary link-btn link-hover-animation <?= 'link-btn-' . $data->link->settings->border_radius ?> <?= $data->link->design->link_class ?>" style="<?= $data->link->design->link_style ?>" data-text-color data-border-shadow data-border-width data-border-radius data-border-style data-border-color data-animation data-background-color data-text-alignment>
                        <?= l('global.view') ?>
                    </a>
                </div>

            </div>
        </div>
    </div>
<?php \Altum\Event::add_content(ob_get_clean(), 'modals') ?>
