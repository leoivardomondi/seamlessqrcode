<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-xl d-flex align-items-center mb-3 mb-xl-0">
            <h1 class="h4 m-0"><?= l('signatures.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('signatures.subheader') ?>">
                    <i class="fa fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-xl-auto d-flex">
            <div>
                <?php if($this->user->plan_settings->signatures_limit != -1 && $data->total_signatures >= $this->user->plan_settings->signatures_limit): ?>
                    <button type="button" class="btn btn-primary disabled" data-toggle="tooltip" title="<?= l('global.info_message.plan_feature_limit') ?>">
                        <i class="fa fa-fw fa-sm fa-plus-circle"></i> <?= l('signatures.create') ?>
                    </button>
                <?php else: ?>
                    <a href="<?= url('signature-create') ?>" class="btn btn-primary">
                        <i class="fa fa-fw fa-sm fa-plus-circle"></i> <?= l('signatures.create') ?>
                    </a>
                <?php endif ?>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>">
                        <i class="fa fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('signatures?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                            <i class="fa fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('signatures?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item">
                            <i class="fa fa-fw fa-sm fa-file-code mr-1"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn <?= count($data->filters->get) ? 'btn-outline-primary' : 'btn-outline-secondary' ?> filters-button dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.filters.header') ?>">
                        <i class="fa fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if(count($data->filters->get)): ?>
                                <a href="<?= url('signatures') ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
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
                                <div class="d-flex justify-content-between">
                                    <label for="filters_project_id" class="small"><?= l('projects.project_id') ?></label>
                                    <a href="<?= url('project-create') ?>" target="_blank" class="small"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
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

    <?php if(count($data->signatures)): ?>
        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('signatures.table.signature') ?></th>
                    <th><?= l('signatures.input.template') ?></th>
                    <th><?= l('projects.project_id') ?></th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($data->signatures as $row): ?>

                    <tr>
                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <?php if($row->settings->image_url): ?>
                                    <a href="<?= url('signature-update/' . $row->signature_id) ?>">
                                        <img src="<?= $row->settings->image_url ?>" class="signature-avatar rounded-circle mr-3" alt="<?= $row->name ?>" />
                                    </a>
                                <?php endif ?>

                                <div class="d-flex flex-column">
                                    <div>
                                        <a href="<?= url('signature-update/' . $row->signature_id) ?>"><?= $row->name ?></a>
                                    </div>

                                    <span class="text-muted"><?= $row->settings->full_name ?? null ?></span>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="<?= $data->signature_templates[$row->template]['icon'] ?> fa-fw fa-sm mr-1"></i> <?= l('signatures.input.template.' . $row->template) ?></span>
                        </td>

                        <td class="text-nowrap">
                            <?php if($row->project_id && isset($data->projects[$row->project_id])): ?>
                                <a href="<?= url('links?project_id=' . $row->project_id) ?>" class="text-decoration-none">
                                    <span class="badge badge-light" style="color: <?= $data->projects[$row->project_id]->color ?> !important;">
                                        <?= $data->projects[$row->project_id]->name ?>
                                    </span>
                                </a>
                            <?php endif ?>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>') ?>">
                                    <i class="fa fa-fw fa-clock text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' : '-')) ?>">
                                    <i class="fa fa-fw fa-history text-muted"></i>
                                </span>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end">
                                <?= include_view(\Altum\Plugin::get('email-signatures')->path . 'views/signatures/signature_dropdown_button.php', ['id' => $row->signature_id, 'resource_name' => $row->name]) ?>
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
                    <img src="<?= ASSETS_FULL_URL . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-4 mb-3" alt="<?= l('signatures.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('signatures.no_data') ?></h2>
                    <p class="text-muted"><?= l('signatures.no_data_help') ?></p>
                </div>
            </div>
        </div>

    <?php endif ?>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'signature',
    'resource_id' => 'signature_id',
    'has_dynamic_resource_name' => true,
    'path' => 'signatures/delete'
]), 'modals'); ?>
