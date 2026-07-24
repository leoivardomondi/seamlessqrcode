<?php defined('ALTUMCODE') || die() ?>

<?php
$teams_limit = $this->user->plan_settings->teams_limit ?? 0;
$teams_can_create = $teams_limit == -1 || $data->total_teams < $teams_limit;
?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= include_view(THEME_PATH . 'views/partials/feature_showcase_upsell.php', [
        'variant' => 'teams',
        'kicker' => 'Teams',
        'title' => 'Collaborate with your team to create, manage and analyze your conversions',
        'subtitle' => 'Seamlessly share and collaboratively manage your codes, pages, reports, and templates with your teammates.',
        'primary_url' => $teams_can_create ? url('team-create') : url('plan/upgrade'),
        'primary_label' => $teams_can_create ? l('teams.create') : 'Upgrade to collaborate',
        'secondary_url' => url('api-documentation/teams'),
        'secondary_label' => 'Learn about teams',
        'cards' => [
            [
                'visual' => 'team_share',
                'title' => 'Collaborate with ease in-platform',
                'copy' => 'No one does business alone. Easily make sure the right people can see assets, make changes, and analyze results',
            ],
            [
                'visual' => 'brand_templates',
                'title' => 'Keep your branding consistent',
                'copy' => 'Ensure your entire organization uses the same code and page designs to follow your brand guidelines.',
            ],
            [
                'visual' => 'campaign_folders',
                'title' => 'Maximize campaign output',
                'copy' => 'Create nested folders for teams and individuals so every campaign is trackable and organized.',
            ],
            [
                'visual' => 'permission_list',
                'title' => 'Control permissions and sharing',
                'copy' => 'Assign admin privileges, manage permissions, create teams and sub-teams, and maintain member lists with ease.',
            ],
        ],
    ]); ?>

    <div class="mt-5">
        <div class="row mb-4">
            <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
                <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-user-gear mr-1"></i> <?= l('teams.header') ?></h1>

                <div class="ml-2">
                    <span data-toggle="tooltip" title="<?= l('teams.subheader') ?>">
                        <i class="fas fa-fw fa-info-circle text-muted"></i>
                    </span>
                </div>
            </div>

            <div class="col-12 col-lg-auto d-print-none">
                <?php if($teams_can_create): ?>
                    <a href="<?= url('team-create') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_teams, $teams_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('teams.create') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= url('plan/upgrade') ?>" class="btn btn-primary">
                        <i class="fas fa-fw fa-arrow-up fa-sm mr-1"></i> <?= 'Upgrade to collaborate' ?>
                    </a>
                <?php endif ?>
            </div>
        </div>

        <?php if(count($data->teams)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.name') ?></th>
                        <th><?= l('teams.table.members') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data->teams as $row): ?>
                        <tr>
                            <td class="text-nowrap">
                                <a href="<?= url('team/' . $row->team_id) ?>"><?= $row->name ?></a>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-light">
                                    <i class="fas fa-fw fa-sm fa-users mr-1"></i>
                                    <?= nr($row->members) ?>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />-')) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/team/team_dropdown_button.php', ['id' => $row->team_id, 'resource_name' => $row->name]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3"><?= $data->pagination ?></div>
        <?php else: ?>
            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                'filters_get' => $data->filters->get ?? [],
                'name' => 'teams',
                'has_secondary_text' => true,
            ]); ?>
        <?php endif ?>
    </div>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'team',
    'resource_id' => 'team_id',
    'has_dynamic_resource_name' => true,
    'path' => 'teams/delete'
]), 'modals'); ?>
