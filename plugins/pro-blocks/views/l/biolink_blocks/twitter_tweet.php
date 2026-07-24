<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2 d-flex justify-content-center">
    <blockquote class="twitter-tweet" data-theme="<?= $data->link->settings->theme ?>">
        <a href="<?= $data->link->location_url ?>"></a>
    </blockquote>
</div>

<?php if(!\Altum\Event::exists_content_type_key('javascript', 'twitter')): ?>
    <?php ob_start() ?>
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'twitter') ?>
<?php endif ?>
