<?php defined('ALTUMCODE') || die() ?>

<?php
$parsedown = new \Parsedown();
$data->link->settings->text = $parsedown->text($data->link->settings->text)
?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <div class="card <?= 'link-btn-' . $data->link->settings->border_radius ?>" style="<?= 'border-width: ' . $data->link->settings->border_width . ';' . 'border-color: ' . $data->link->settings->border_color . ';' . 'border-style: ' . $data->link->settings->border_style . ';' . 'background: ' . $data->link->settings->background_color . ';' ?>" data-border-width data-border-radius data-border-style data-border-color data-border-shadow data-background-color>
        <div class="card-body text-break" style="color: <?= $data->link->settings->text_color ?>; text-align: <?= ($data->link->settings->text_alignment ?? 'center') ?>;" data-text data-text-color data-text-alignment>
            <?= $data->link->settings->text ?>
        </div>
    </div>
</div>
