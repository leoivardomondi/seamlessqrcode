<?php defined('ALTUMCODE') || die() ?>

<?php if(!\Altum\Event::exists_content_type_key('javascript', 'select2')): ?>
    <?php ob_start() ?>
    <link href="<?= ASSETS_FULL_URL . 'css/libraries/select2.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen">
    <?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

    <?php ob_start() ?>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/select2.min.js?v=' . PRODUCT_CODE ?>"></script>

    <script>
        'use strict';

        /* Custom select implementation */
        $('select:not([multiple="multiple"]):not([class="input-group-text"]):not([class="custom-select custom-select-sm"]):not([class^="ql"]):not([data-is-not-custom-select])').each(function() {
            let $select = $(this);
            $select.select2({
                dir: <?= json_encode(l('direction')) ?>,
                minimumResultsForSearch: 5,
            });

            /* Make sure to trigger the select when the label is clicked as well */
            let selectId = $select.attr('id');
            if (selectId) {
                $('label[for="' + selectId + '"]').on('click', function(event) {
                    event.preventDefault();
                    $select.select2('open');
                });
            }
        });
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
<?php endif ?>
