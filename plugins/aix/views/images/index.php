<?php defined('ALTUMCODE') || die() ?>


<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="mb-4">
        <div class="row justify-content-between">
            <div class="col-12 col-sm-6 col-xl-4 mb-3">
                <div class="card h-100 position-relative">
                    <div class="card-body d-flex">
                        <div>
                            <div class="card border-0 bg-primary-100 mr-3 position-static">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-icons fa-lg text-primary-600"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="card-title h4 m-0"><?= nr($data->total_images) ?></div>
                            <span class="text-muted"><?= l('images.widget.total') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 mb-3">
                <div class="card h-100 position-relative" data-toggle="tooltip" title="<?= l('images.widget.this_month') ?>">
                    <div class="card-body d-flex">
                        <div>
                            <div class="card border-0 bg-primary-100 mr-3 position-static">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-images fa-lg text-primary-600"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="card-title h4 m-0"><?= nr($data->images_current_month) ?></div>
                            <span class="text-muted"><?= l('images.widget.images_current_month') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 mb-3">
                <div class="card h-100 position-relative" data-toggle="tooltip" title="<?= l('images.widget.this_month') ?>">
                    <div class="card-body d-flex">
                        <div>
                            <div class="card border-0 bg-primary-100 mr-3 position-static">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-camera-retro fa-lg text-primary-600"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="card-title h4 m-0"><?= $this->user->plan_settings->images_per_month_limit != -1 ? nr($data->available_images) : l('global.unlimited') ?></div>
                            <span class="text-muted"><?= l('images.widget.available_images') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 col-xl d-flex align-items-center mb-3 mb-xl-0">
            <h1 class="h4 m-0"><i class="fas fa-fw fa-xs fa-icons mr-1"></i> <?= l('images.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('images.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-xl-auto d-flex">
            <div>
                <?php if(($this->user->plan_settings->images_per_month_limit != -1 && $data->images_current_month >= $this->user->plan_settings->images_per_month_limit)): ?>
                    <button type="button" class="btn btn-primary disabled" data-toggle="tooltip" title="<?= l('global.info_message.plan_feature_limit') ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('images.create') ?>
                    </button>
                <?php else: ?>
                    <a href="<?= url('image-create') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->images_current_month, $this->user->plan_settings->images_per_month_limit) ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('images.create') ?>
                    </a>
                <?php endif ?>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>">
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('images?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('images?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-code mr-1"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn <?= count($data->filters->get) ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.filters.header') ?>">
                        <i class="fas fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown custom-scrollbar">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if(count($data->filters->get)): ?>
                                <a href="<?= url('images') ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                                <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                <select name="search_by" id="filters_search_by" class="custom-select custom-select-sm">
                                    <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_style" class="small"><?= l('images.style') ?></label>
                                <select name="style" id="filters_style" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->ai_images_styles as $key => $value): ?>
                                        <option value="<?= $key ?>" <?= isset($data->filters->filters['style']) && $data->filters->filters['style'] == $key ? 'selected="selected"' : null ?>><?= l('images.style.' . $key) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_artist" class="small"><?= l('images.artist') ?></label>
                                <select name="artist" id="filters_artist" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach(settings()->aix->images_available_artists as $artist): ?>
                                        <option value="<?= $artist ?>" <?= isset($data->filters->filters['artist']) && $data->filters->filters['artist'] == $artist ? 'selected="selected"' : null ?>><?= $artist ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_lighting" class="small"><?= l('images.lighting') ?></label>
                                <select name="lighting" id="filters_lighting" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->ai_images_lighting as $key => $value): ?>
                                        <option value="<?= $key ?>" <?= isset($data->filters->filters['lighting']) && $data->filters->filters['lighting'] == $key ? 'selected="selected"' : null ?>><?= l('images.lighting.' . $key); ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_mood" class="small"><?= l('images.mood') ?></label>
                                <select name="mood" id="filters_mood" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->ai_images_moods as $key => $value): ?>
                                        <option value="<?= $key ?>" <?= isset($data->filters->filters['mood']) && $data->filters->filters['mood'] == $key ? 'selected="selected"' : null ?>><?= l('images.mood.' . $key); ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_size" class="small"><?= l('images.size') ?></label>
                                <select name="size" id="filters_size" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach(['256x256', '512x512', '1024x1024', '1792x1024', '1024x1792'] as $key): ?>
                                        <option value="<?= $key ?>" <?= isset($data->filters->filters['size']) && $data->filters->filters['size'] == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <div class="d-flex justify-content-between">
                                    <label for="filters_project_id" class="small"><?= l('projects.project_id') ?></label>
                                    <a href="<?= url('project-create') ?>" target="_blank" class="small"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                                </div>
                                <select name="project_id" id="filters_project_id" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->projects as $project_id => $project): ?>
                                        <option value="<?= $project_id ?>" <?= isset($data->filters->filters['project_id']) && $data->filters->filters['project_id'] == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="filters_results_per_page" class="custom-select custom-select-sm">
                                    <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                        <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4 mt-4">
                                <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($data->images)): ?>
        <div class="table-responsive table-custom-container custom-scrollbar">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('images.image') ?></th>
                    <th><?= l('images.size') ?></th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($data->images as $row): ?>

                    <tr>
                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <a href="<?= \Altum\Uploads::get_full_url('images') . $row->image ?>" target="_blank">
                                    <img src="<?= \Altum\Uploads::get_full_url('images') . $row->image ?>" class="img-fluid rounded mr-3" style="width: 50px; height: 50px;min-width: 50px; min-height: 50px;" data-toggle="tooltip" title="<?= l('global.view') ?>" alt="<?= $row->input ?>" />
                                </a>

                                <div class="d-flex flex-column">
                                    <a href="<?= url('image-update/' . $row->image_id) ?>"><?= $row->name ?></a>
                                    <small class="text-muted" data-toggle="tooltip" title="<?= string_truncate($row->input, 256) ?>"><?= string_truncate($row->input, 32) ?></small>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><?= $row->size ?></span>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>') ?>">
                                    <i class="fas fa-fw fa-clock text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' : '-')) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end">
                                <?= include_view(\Altum\Plugin::get('aix')->path . 'views/images/image_dropdown_button.php', ['id' => $row->image_id, 'resource_name' => $row->name, 'image' => $row->image, 'image_url' => \Altum\Uploads::get_full_url('images') . $row->image]) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>

                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-4 mb-3" alt="<?= l('images.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('images.no_data') ?></h2>
                    <p class="text-muted"><?= l('images.no_data_help') ?></p>
                </div>
            </div>
        </div>

    <?php endif ?>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'image',
    'resource_id' => 'image_id',
    'has_dynamic_resource_name' => true,
    'path' => 'images/delete'
]), 'modals'); ?>
