<?php defined('ALTUMCODE') || die() ?>

<?php
$domains_limit = $this->user->plan_settings->domains_limit ?? 0;
$domains_can_create = $domains_limit == -1 || $data->total_domains < $domains_limit;
?>

<section class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= include_view(THEME_PATH . 'views/partials/feature_showcase_upsell.php', [
        'variant' => 'domains',
        'kicker' => 'Domains',
        'title' => 'Better expose your brand to scanners with white labeled domains',
        'subtitle' => 'Better expose your brand to scanners and page visitors with white labeled, custom domains',
        'primary_url' => $domains_can_create ? url('domain-create') : url('plan/upgrade'),
        'primary_label' => $domains_can_create ? l('domains.create') : 'Upgrade to add domains',
        'secondary_url' => url('api-documentation/domains'),
        'secondary_label' => 'Learn about domains',
        'cards' => [
            [
                'visual' => 'domain_phone',
                'title' => 'Drive better results',
                'copy' => 'Set your own domains as the redirect for codes and pages.',
            ],
            [
                'visual' => 'domain_roi',
                'title' => 'Increase your marketing ROI',
                'copy' => 'Better brand visibility equals increased consumer engagement.',
            ],
            [
                'visual' => 'brand_phone',
                'title' => 'Reinforce your branding',
                'copy' => 'Your audience trusts your brand. Use a domain that they are familiar with.',
            ],
            [
                'visual' => 'share_domain',
                'title' => 'Share domains across teams',
                'copy' => 'Share domains with your teammates and organization to maintain brand consistency.',
            ],
        ],
    ]); ?>

    <div class="mt-5">
        <div class="row mb-4">
            <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
                <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-globe mr-1"></i> <?= l('domains.header') ?></h1>

                <div class="ml-2">
                    <span data-toggle="tooltip" title="<?= l('domains.subheader') ?>">
                        <i class="fas fa-fw fa-info-circle text-muted"></i>
                    </span>
                </div>
            </div>

            <div class="col-12 col-lg-auto d-print-none">
                <?php if($domains_can_create): ?>
                    <a href="<?= url('domain-create') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_domains, $domains_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('domains.create') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= url('plan/upgrade') ?>" class="btn btn-primary">
                        <i class="fas fa-fw fa-arrow-up fa-sm mr-1"></i> <?= 'Upgrade to add domains' ?>
                    </a>
                <?php endif ?>
            </div>
        </div>

        <?php if(count($data->domains)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('domains.table.host') ?></th>
                        <th><?= l('global.status') ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data->domains as $row): ?>
                        <tr>
                            <td class="text-nowrap">
                                <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->host) ?>" class="img-fluid icon-favicon mr-1" loading="lazy" />
                                <a href="<?= url('domain-update/' . $row->domain_id) ?>"><?= $row->host ?></a>
                                <a href="<?= 'https://' . $row->host ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i></a>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->is_enabled): ?>
                                    <span class="badge badge-success"><i class="fas fa-fw fa-check"></i> <?= l('domains.table.is_enabled_active') ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><i class="fas fa-fw fa-eye-slash"></i> <?= l('domains.table.is_enabled_pending') ?></span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap text-muted">
                                <a href="<?= url('links?domain_id=' . $row->domain_id) ?>" class="mr-2" data-toggle="tooltip" title="<?= l('links.title') ?>">
                                    <i class="fas fa-fw fa-link text-muted"></i>
                                </a>
                            </td>

                            <td class="text-nowrap text-muted">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />-')) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/domains/domain_dropdown_button.php', ['id' => $row->domain_id]) ?>
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
                'name' => 'domains',
                'has_secondary_text' => true,
            ]); ?>
        <?php endif ?>
    </div>
</section>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/domains/domain_delete_modal.php'), 'modals'); ?>
