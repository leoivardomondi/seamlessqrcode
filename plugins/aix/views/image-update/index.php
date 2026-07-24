<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('images') ?>"><?= l('images.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('image_update.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-icons mr-1"></i> <?= sprintf(l('global.update_x'), $data->image->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <a href="#" id="duplicate" class="btn btn-link text-secondary" data-toggle="tooltip" title="<?= l('global.duplicate') ?>">
                <i class="fas fa-fw fa-sm fa-copy"></i>
            </a>

            <?= include_view(\Altum\Plugin::get('aix')->path . 'views/images/image_dropdown_button.php', ['id' => $data->image->image_id, 'resource_name' => $data->image->name, 'image' => $data->image->image, 'image_url' => \Altum\Uploads::get_full_url('images') . $data->image->image]) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->image->name ?>" required="required" />
                </div>

                <div class="form-group">
                    <label for="input"><i class="fas fa-fw fa-paragraph fa-sm text-muted mr-1"></i> <?= l('images.input') ?></label>
                    <div class="card bg-gray-100">
                        <div class="card-body">
                            <?= $data->image->input ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-fw fa-image fa-sm text-muted mr-1"></i> <?= l('images.image') ?></label>

                    <div class="position-relative">
                        <div style="background: url('<?= \Altum\Uploads::get_full_url('images') . $data->image->image ?>'); z-index: 1; background-size: cover; width: 100%; height: 100%; opacity: .25; filter: blur(10px);" class="position-absolute"></div>
                        <div class="text-center w-100 h-100 p-5 position-absolute" style="z-index: 2;">
                            <img src="<?= \Altum\Uploads::get_full_url('images') . $data->image->image ?>" class="img-fluid rounded shadow-lg" alt="<?= $data->image->input ?>" />
                        </div>
                        <div class="text-center p-5">
                            <img src="<?= \Altum\Uploads::get_full_url('images') . $data->image->image ?>" class="img-fluid rounded" alt="<?= $data->image->input ?>" />
                        </div>
                    </div>
                </div>

                <?php if($data->image->settings->variants > 1): ?>
                    <div class="form-group">
                        <label for="variants"><i class="fas fa-fw fa-list-ol fa-sm text-muted mr-1"></i> <?= l('images.variants') ?> (<?= $data->image->settings->variants ?>)</label>
                        <div class="row">
                            <?php foreach($data->variants as $image): ?>
                                <div class="col-4 col-lg-2">
                                    <a href="<?= url('image-update/' . $image->image_id) ?>">
                                        <img src="<?= \Altum\Uploads::get_full_url('images') . $image->image ?>" class="img-fluid rounded" alt="<?= $image->input ?>" data-toggle="tooltip" title="<?= $image->name ?>" />
                                    </a>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endif ?>

                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="size"><i class="fas fa-fw fa-paint-brush fa-sm text-muted mr-1"></i> <?= l('images.style') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= $data->image->style ? l('images.style.' . $data->image->style) : l('global.none') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="size"><i class="fas fa-fw fa-user fa-sm text-muted mr-1"></i> <?= l('images.artist') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= $data->image->artist ? $data->image->artist : l('global.none') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="size"><i class="fas fa-fw fa-bolt fa-sm text-muted mr-1"></i> <?= l('images.lighting') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= $data->image->lighting ? l('images.lighting.' . $data->image->lighting) : l('global.none') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="size"><i class="fas fa-fw fa-smile fa-sm text-muted mr-1"></i> <?= l('images.mood') ?></label>
                            <div class="card bg-gray-100">
                                <div class="card-body">
                                    <?= $data->image->mood ? l('images.mood.' . $data->image->mood) : l('global.none') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="size"><i class="fas fa-fw fa-expand-arrows-alt fa-sm text-muted mr-1"></i> <?= l('images.size') ?></label>
                    <div class="card bg-gray-100">
                        <div class="card-body">
                            <?= $data->image->size ?>
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
                            <option value="<?= $project_id ?>" <?= $data->image->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
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
    <input type="hidden" name="name" value="<?= $data->image->name ?>" />
    <input type="hidden" name="input" value="<?= $data->image->input ?>" />
    <input type="hidden" name="project_id" value="<?= $data->image->project_id ?>" />
    <input type="hidden" name="size" value="<?= $data->image->size ?>" />
    <input type="hidden" name="style" value="<?= $data->image->style ?>" />
    <input type="hidden" name="artist" value="<?= $data->image->artist ?>" />
    <input type="hidden" name="lighting" value="<?= $data->image->lighting ?>" />
    <input type="hidden" name="variants" value="<?= $data->image->settings->variants ?>" />
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

    document.querySelector('#duplicate').href = `${site_url}image-create?${query.toString()}`;
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'image',
    'resource_id' => 'image_id',
    'has_dynamic_resource_name' => true,
    'path' => 'images/delete'
]), 'modals'); ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
