<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-2">
    <div class="fb-post bg-white" data-href="<?= $data->link->location_url ?>"></div>
</div>

<?php if(!\Altum\Event::exists_content_type_key('javascript', 'facebook')): ?>
    <?php ob_start() ?>
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v14.0"></script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'facebook') ?>
<?php endif ?>

