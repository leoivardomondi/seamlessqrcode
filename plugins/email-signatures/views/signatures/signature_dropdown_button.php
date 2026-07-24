<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fa fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a href="<?= url('signature-update/' . $data->id) ?>" class="dropdown-item"><i class="fa fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#signature_duplicate_modal" data-signature-id="<?= $data->id ?>" class="dropdown-item"><i class="fa fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
        <a href="#" data-toggle="modal" data-target="#signature_delete_modal" data-signature-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fa fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php if(!\Altum\Event::exists_content_type_key('modals', 'signature_duplicate_modal')): ?>
    <?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'signature_duplicate_modal', 'resource_id' => 'signature_id', 'path' => 'signatures/duplicate']), 'modals', 'signature_duplicate_modal'); ?>
<?php endif ?>
