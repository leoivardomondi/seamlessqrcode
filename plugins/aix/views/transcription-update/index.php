<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('transcriptions') ?>"><?= l('transcriptions.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('transcription_update.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-microphone-lines mr-1"></i> <?= sprintf(l('global.update_x'), $data->transcription->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <?= include_view(\Altum\Plugin::get('aix')->path . 'views/transcriptions/transcription_dropdown_button.php', ['id' => $data->transcription->transcription_id, 'resource_name' => $data->transcription->name]) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->transcription->name ?>" required="required" />
                </div>

                <?php if($data->transcription->input): ?>
                <div class="form-group">
                    <label for="input"><i class="fas fa-fw fa-paragraph fa-sm text-muted mr-1"></i> <?= l('transcriptions.input') ?></label>
                    <div class="card bg-gray-100">
                        <div class="card-body">
                            <?= $data->transcription->input ?>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <div class="form-group">
                    <label for="content"><i class="fas fa-fw fa-robot fa-sm text-muted mr-1"></i> <?= l('transcriptions.content') ?></label>
                    <div id="quill_container">
                        <div id="quill" class="h-auto"></div>
                    </div>
                    <textarea name="content" id="content" class="form-control d-none" rows="10"><?= $data->transcription->content ?></textarea>
                </div>

                <div class="form-group">
                    <button
                            type="button"
                            class="btn btn-block btn-outline-primary"
                            data-toggle="tooltip"
                            title="<?= l('global.clipboard_copy') ?>"
                            aria-label="<?= l('global.clipboard_copy') ?>"
                            data-copy="<?= l('global.clipboard_copy') ?>"
                            data-copied="<?= l('global.clipboard_copied') ?>"
                            data-clipboard-target="#content"
                            data-clipboard-text
                    >
                        <i class="fas fa-fw fa-sm fa-copy"></i> <?= l('global.clipboard_copy') ?>
                    </button>
                </div>

                <?php if($data->transcription->language): ?>
                <div class="form-group">
                    <label for="size"><i class="fas fa-fw fa-user fa-sm text-muted mr-1"></i> <?= l('global.language') ?></label>
                    <div class="card bg-gray-100">
                        <div class="card-body">
                            <?= $data->ai_transcriptions_languages[$data->transcription->language] ?>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <?php if($data->transcription->words): ?>
                    <div class="form-group">
                        <label for="size"><i class="fas fa-fw fa-feather fa-sm text-muted mr-1"></i> <?= l('transcriptions.words') ?></label>
                        <div class="card bg-gray-100">
                            <div class="card-body">
                                <?= nr($data->transcription->words) ?>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <div class="d-flex flex-column flex-xl-row justify-content-between">
                        <label for="project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                        <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                    </div>
                    <select id="project_id" name="project_id" class="custom-select">
                        <option value=""><?= l('global.none') ?></option>
                        <?php foreach($data->projects as $project_id => $project): ?>
                            <option value="<?= $project_id ?>" <?= $data->transcription->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/quill.snow.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/quill.min.js' ?>"></script>

<script>
    'use strict';

    let quill = new Quill('#quill', {
        theme: 'snow',
        modules: {
            toolbar: [
                ["bold", "italic", "underline", "strike"],
                [{ "header": 1 }, { "header": 2 }],
                ["blockquote", "code-block"],
                [{ "list": "ordered" }, { "list": "bullet" }, { "indent": "-1" }, { "indent": "+1" }],
                [{ "direction": "rtl" }, { "align": "" }, { "align": "center" }, { "align": "right" }, { "align": "justify" }],
                [{ "script": "sub" }, { "script": "super" }],
                ["link", "clean"]
            ]
        },
    });

    quill.root.innerHTML = document.querySelector('#content').value;
    quill.enable(true);
    document.querySelector('#quill_container').classList.remove('d-none');
    document.querySelector('#content').classList.add('d-none');

    /* Handle form submission with the editor */
    document.querySelector('form').addEventListener('submit', event => {
        document.querySelector('#content').value = quill.root.innerHTML;
    });

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'transcription',
    'resource_id' => 'transcription_id',
    'has_dynamic_resource_name' => true,
    'path' => 'transcriptions/delete'
]), 'modals'); ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
