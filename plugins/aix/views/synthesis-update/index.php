<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('syntheses') ?>"><?= l('syntheses.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('synthesis_update.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-voicemail mr-1"></i> <?= sprintf(l('global.update_x'), $data->synthesis->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <a href="#" id="duplicate" class="btn btn-link text-secondary" data-toggle="tooltip" title="<?= l('global.duplicate') ?>">
                <i class="fas fa-fw fa-sm fa-copy"></i>
            </a>

            <?= include_view(\Altum\Plugin::get('aix')->path . 'views/syntheses/synthesis_dropdown_button.php', ['id' => $data->synthesis->synthesis_id, 'resource_name' => $data->synthesis->name, 'file' => $data->synthesis->file, 'synthesis_url' => \Altum\Uploads::get_full_url('syntheses') . $data->synthesis->file]) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->synthesis->name ?>" required="required" />
                </div>

                <div class="form-group">
                    <label for="input"><i class="fas fa-fw fa-paragraph fa-sm text-muted mr-1"></i> <?= l('syntheses.input') ?></label>
                    <div class="card bg-gray-100">
                        <div class="card-body">
                            <?= $data->synthesis->input ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-fw fa-voicemail fa-sm text-muted mr-1"></i> <?= l('syntheses.synthesis') ?></label>
                    <div>
                        <audio class="w-100" controls preload="auto">
                            <source src="<?= \Altum\Uploads::get_full_url('syntheses') . $data->synthesis->file ?>" type="audio/mpeg">
                        </audio>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="size"><i class="fas fa-fw fa-language fa-sm text-muted mr-1"></i> <?= l('syntheses.language') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= $data->ai_languages[$data->synthesis->language] ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="size"><i class="fas fa-fw fa-text-width fa-sm text-muted mr-1"></i> <?= l('syntheses.characters') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= nr($data->synthesis->characters) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="voice_id"><i class="fas fa-fw fa-volume-up fa-sm text-muted mr-1"></i> <?= l('syntheses.voice_id') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= ($data->ai_voices[$data->synthesis->voice_id]['name'] ?: $data->synthesis->voice_id) . ' (' . l('syntheses.voice_gender.' . $data->ai_voices[$data->synthesis->voice_id]['gender']) . ')' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="size"><i class="fas fa-fw fa-podcast fa-sm text-muted mr-1"></i> <?= l('syntheses.voice_engine') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= l('syntheses.voice_engine.' . str_replace('-', '_', $data->synthesis->voice_engine)) ?>
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
                            <option value="<?= $project_id ?>" <?= $data->synthesis->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
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
    <input type="hidden" name="name" value="<?= $data->synthesis->name ?>" />
    <input type="hidden" name="input" value="<?= $data->synthesis->input ?>" />
    <input type="hidden" name="project_id" value="<?= $data->synthesis->project_id ?>" />
    <input type="hidden" name="language" value="<?= $data->synthesis->language ?>" />
    <input type="hidden" name="voice_id" value="<?= $data->synthesis->voice_id ?>" />
    <input type="hidden" name="voice_engine" value="<?= $data->synthesis->voice_engine ?>" />
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

    document.querySelector('#duplicate').href = `${site_url}synthesis-create?${query.toString()}`;
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'synthesis',
    'resource_id' => 'synthesis_id',
    'has_dynamic_resource_name' => true,
    'path' => 'syntheses/delete'
]), 'modals'); ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
