<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <div class="card <?= 'link-btn-' . $data->link->settings->border_radius ?>" style="<?= 'border-width: ' . $data->link->settings->border_width . ';' . 'border-color: ' . $data->link->settings->border_color . ';' . 'border-style: ' . $data->link->settings->border_style . ';' . 'background: ' . $data->link->settings->background_color . ';' ?>" data-border-width data-border-radius data-border-style data-border-color data-border-shadow data-animation data-background-color>
        <div class="card-body d-flex flex-column" style="<?= 'text-align: ' . $data->link->settings->text_alignment . ';' ?>" data-text-alignment>
            <span class="h5 mb-2 font-weight-bolder" style="<?= 'color: ' . $data->link->settings->title_color . ';' ?>" data-title data-title-color><?= $data->link->settings->title ?></span>

            <div class="mb-3">
                <?php for($i = 1; $i <= $data->link->settings->stars; $i++): ?>
                <i class="fa fa-fw fa-star mr-1" style="<?= 'color: ' . $data->link->settings->stars_color . ';' ?>" data-stars-color></i>
                <?php endfor ?>
            </div>

            <span class="mb-4" style="<?= 'color: ' . $data->link->settings->description_color . ';' ?>" data-description data-description-color><?= nl2br($data->link->settings->description) ?></span>

            <?php
            $justify_content_class = 'justify-content-center';
            switch($data->link->settings->text_alignment) {
                case 'left':
                case 'justify':
                    $justify_content_class = 'justify-content-start';
                    break;

                case 'right':
                    $justify_content_class = 'justify-content-end';
                    break;
            }
            ?>
            <div class="d-flex align-items-center <?= $justify_content_class ?>">
                <div class="mr-3">
                    <img src="<?= UPLOADS_FULL_URL . 'block_images/' . $data->link->settings->image ?>" class="link-review-image" alt="<?= $data->link->settings->author_name ?>" loading="lazy" />
                </div>
                <div class="d-flex flex-column">
                    <span class="font-weight-bold" style="<?= 'color: ' . $data->link->settings->author_name_color . ';' ?>" data-author_name data-author_name-color><?= $data->link->settings->author_name ?></span>
                    <span class="small" style="<?= 'color: ' . $data->link->settings->author_description_color . ';' ?>" data-author_description data-author_description-color><?= $data->link->settings->author_description ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
