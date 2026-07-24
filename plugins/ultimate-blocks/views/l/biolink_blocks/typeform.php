<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <div class="w-100 link-iframe-round" style="height: 500px" data-tf-widget="<?= $data->embed ?>"></div>
</div>

<?php if(!\Altum\Event::exists_content_type_key('javascript', 'tiktok')): ?>
    <?php ob_start() ?>
    <script defer src="//embed.typeform.com/next/embed.js"></script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'tiktok') ?>
<?php endif ?>
