<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('documents') ?>"><?= l('documents.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('document_update.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-robot mr-1"></i> <?= sprintf(l('global.update_x'), $data->document->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <a href="#" id="duplicate" class="btn btn-link text-secondary" data-toggle="tooltip" title="<?= l('global.duplicate') ?>">
                <i class="fas fa-fw fa-sm fa-copy"></i>
            </a>

            <?= include_view(\Altum\Plugin::get('aix')->path . 'views/documents/document_dropdown_button.php', ['id' => $data->document->document_id, 'resource_name' => $data->document->name]) ?>
        </div>
    </div>


    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->document->name ?>" required="required" />
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="content"><i class="fas fa-fw fa-robot fa-sm text-muted mr-1"></i> <?= l('documents.content') ?></label>

                        <label>
                            <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-toggle="tooltip"
                                    title="<?= l('global.clipboard_copy') ?>"
                                    aria-label="<?= l('global.clipboard_copy') ?>"
                                    data-copy="<?= l('global.clipboard_copy') ?>"
                                    data-copied="<?= l('global.clipboard_copied') ?>"
                                    data-clipboard-target="#quill"
                                    data-clipboard-text
                            >
                                <i class="fas fa-fw fa-sm fa-copy"></i> <?= l('global.clipboard_copy') ?>
                            </button>
                        </label>
                    </div>

                    <?php if(in_array($data->document->template_category_id, [5, 1111])): ?>
                        <textarea name="content" id="content" class="form-control" rows="10"><?= $data->document->content ?></textarea>
                    <?php else: ?>
                        <div id="quill_container">
                            <div id="quill" class="h-auto"></div>
                        </div>
                        <textarea name="content" id="content" class="form-control d-none" rows="10"><?= $data->document->content ?></textarea>
                    <?php endif ?>
                </div>

                <div class="form-group">
                    <label for="type"><i class="fas fa-fw fa-moon fa-sm text-muted mr-1"></i> <?= l('global.type') ?></label>
                    <div class="card border-0" style="background: <?= $data->templates_categories[$data->document->template_category_id]->background ?>; color: <?= $data->templates_categories[$data->document->template_category_id]->color ?>;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="font-weight-bold">
                                <i class="<?= $data->templates[$data->document->type]->icon ?> fa-fw"></i> <?= $data->templates[$data->document->type]->settings->translations->{\Altum\Language::$name}->name ?>
                            </div>

                            <div>
                                <a href="<?= url('document-create?type=' . $data->document->type) ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('documents.create') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php foreach($data->templates as $template_id => $template): ?>
                    <?php if($data->document->type != $template_id) continue; ?>

                    <?php if($data->document->input): ?>
                        <?php foreach($template->settings->inputs as $input_key => $input): ?>
                            <div class="form-group" data-type="<?= $template_id ?>">
                                <label><i class="<?= $input->icon ?> fa-sm text-muted mr-1"></i> <?= $input->translations->{\Altum\Language::$name}->name ?></label>
                                <div class="card bg-gray-100">
                                    <div class="card-body">
                                        <?= $data->document->input->{$input_key} ?? null ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                <?php endforeach ?>

                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="language"><i class="fas fa-fw fa-language fa-sm text-muted mr-1"></i> <?= l('global.language') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= $data->document->settings->language ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="words"><i class="fas fa-fw fa-feather fa-sm text-muted mr-1"></i> <?= l('documents.words') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= nr($data->document->words) ?> <span data-toggle="tooltip" title="<?= l('documents.words_help') ?>"><i class="fas fa-fw fa-sm fa-info-circle text-muted"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="creativity_level"><i class="fas fa-fw fa-lightbulb fa-sm text-muted mr-1"></i> <?= l('documents.creativity_level') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= l('documents.creativity_level.' . $data->document->settings->creativity_level) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="variants"><i class="fas fa-fw fa-list-ol fa-sm text-muted mr-1"></i> <?= l('documents.variants') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= nr($data->document->settings->variants) . ' - ' . l('documents.max_words_per_variant')  . ': ' . ($data->document->settings->max_words_per_variant ?? l('global.unlimited')) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-column flex-xl-row justify-content-between">
                        <label for="project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                        <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                    </div>
                    <select id="project_id" name="project_id" class="custom-select">
                        <option value=""><?= l('global.none') ?></option>
                        <?php foreach($data->projects as $project_id => $project): ?>
                            <option value="<?= $project_id ?>" <?= $data->document->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<form id="data" action="" method="post" role="form">
    <input type="hidden" name="name" value="<?= $data->document->name ?>" />
    <input type="hidden" name="type" value="<?= $data->document->type ?>" />
    <input type="hidden" name="language" value="<?= $data->document->settings->language ?>" />
    <input type="hidden" name="project_id" value="<?= $data->document->project_id ?>" />
    <input type="hidden" name="template_id" value="<?= $data->document->template_id ?>" />
    <input type="hidden" name="template_category_id" value="<?= $data->document->template_category_id ?>" />
    <input type="hidden" name="variants" value="<?= $data->document->settings->variants ?>" />
    <input type="hidden" name="max_words_per_variant" value="<?= $this->user->plan_settings->words_per_month_limit == -1 ? $data->document->settings->max_words_per_variant :  ($data->document->settings->max_words_per_variant > $data->available_words ? $data->available_words : $data->document->settings->max_words_per_variant) ?>" />
    <input type="hidden" name="creativity_level" value="<?= $data->document->settings->creativity_level ?>" />
    <input type="hidden" name="creativity_level_custom" value="<?= $data->document->settings->creativity_level_custom ?>" />
    <?php if($data->document->input): ?>
        <?php foreach($data->document->input as $input_key => $input_value): ?>
            <input type="hidden" name="<?= $data->document->type . '_' . $input_key ?>" value="<?= $input_value ?>" />
        <?php endforeach ?>
    <?php endif ?>
</form>


<?php ob_start() ?>
<script>
    'use strict';

    let query = new URLSearchParams();
    document.querySelectorAll('#data input').forEach(element => {
        if(element.value) {
            query.append(element.getAttribute('name'), element.value);
        }
    })

    document.querySelector('#duplicate').href = `${site_url}document-create?${query.toString()}`;
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

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
    // quill.setText(document.querySelector('#content').value);
    document.querySelector('#quill_container').classList.remove('d-none');
    document.querySelector('#content').classList.add('d-none');

    /* Handle form submission with the editor */
    document.querySelector('form').addEventListener('submit', event => {
        document.querySelector('#content').value = quill.root.innerHTML;
    });

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>


<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'document',
    'resource_id' => 'document_id',
    'has_dynamic_resource_name' => true,
    'path' => 'documents/delete'
]), 'modals'); ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
