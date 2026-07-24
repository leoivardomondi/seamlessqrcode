<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <div class="card <?= 'link-btn-' . $data->link->settings->border_radius ?>" style="<?= 'border-width: ' . $data->link->settings->border_width . ';' . 'border-color: ' . $data->link->settings->border_color . ';' . 'border-style: ' . $data->link->settings->border_style . ';' . 'background: ' . $data->link->settings->background_color . ';' ?>" data-border-width data-border-radius data-border-style data-border-color data-border-shadow data-animation data-background-color>
        <div class="card-body d-flex flex-column" style="<?= 'text-align: ' . $data->link->settings->text_alignment . ';' ?>" data-text-alignment>
            <ol class="position-relative list-style-none" style="<?= 'border-left: 2px solid ' . $data->link->settings->line_color . ' !important;' ?>" data-line-border-color>
                <?php foreach($data->link->settings->items as $key => $item): ?>
                    <li class="mb-4 ml-3 d-flex flex-column">
                        <div class="position-absolute link-btn-round" style="width: .75rem; height: .75rem; left: -0.45rem; <?= 'background: ' . $data->link->settings->line_color . ';' ?>" data-line-background-color></div>
                        <time class="mb-1 small" style="<?= 'color: ' . $data->link->settings->date_color . ';' ?>" data-date-color><?= $item->date ?></time>
                        <span class="h5 font-weight-bolder" style="<?= 'color: ' . $data->link->settings->title_color . ';' ?>" data-title-color><?= $item->title ?></span>
                        <p class="" style="<?= 'color: ' . $data->link->settings->description_color . ';' ?>" data-description-color><?= $item->description ?></p>
                    </li>
                <?php endforeach ?>
            </ol>
        </div>
    </div>
</div>
