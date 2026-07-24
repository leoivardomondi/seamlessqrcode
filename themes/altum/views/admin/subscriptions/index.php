<?php defined('ALTUMCODE') || die() ?>

<?php
$status_badges = [
    'trialing' => 'badge-info',
    'active' => 'badge-success',
    'lifetime' => 'badge-success',
    'non_renewing' => 'badge-warning',
    'paused' => 'badge-secondary',
    'past_due' => 'badge-danger',
    'canceled' => 'badge-light',
    'expired' => 'badge-light',
];

$format_status = function($status) {
    return l('subscription_status.' . $status, null, true) ?? ucfirst(str_replace('_', ' ', $status));
};

$revenue_statuses = ['trialing', 'active', 'past_due'];
$get_default_currency_mrr = function($row) {
    $default_currency = settings()->payment->default_currency;
    $total_amount = $row->total_amount_default_currency !== null
        ? (float) $row->total_amount_default_currency
        : ($row->currency == $default_currency ? (float) $row->total_amount : null);

    if($total_amount === null) {
        return null;
    }

    return match($row->frequency) {
        'monthly' => $total_amount,
        'quarterly' => $total_amount / 3,
        'biannual' => $total_amount / 6,
        'annual' => $total_amount / 12,
        default => null,
    };
};
?>

<div class="d-flex flex-column flex-md-row justify-content-between mb-4">
    <h1 class="h3 mb-3 mb-md-0"><i class="fas fa-fw fa-xs fa-repeat text-primary-900 mr-2"></i> <?= l('admin_subscriptions.header') ?></h1>

    <div class="d-flex position-relative d-print-none">
        <div>
            <div class="dropdown">
                <button type="button" class="btn btn-gray-300 dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                    <i class="fas fa-fw fa-sm fa-download"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right d-print-none">
                    <a href="<?= url('admin/subscriptions?' . $data->filters->get_get() . '&export=csv') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled' ?>">
                        <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                    </a>
                    <a href="<?= url('admin/subscriptions?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled' ?>">
                        <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="ml-3">
            <div class="dropdown">
                <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-secondary' : 'btn-gray-300' ?> filters-button dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.filters.header') ?>" data-tooltip-hide-on-click>
                    <i class="fas fa-fw fa-sm fa-filter"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                    <div class="dropdown-header d-flex justify-content-between">
                        <span class="h6 m-0"><?= l('global.filters.header') ?></span>
                        <?php if($data->filters->has_applied_filters): ?>
                            <a href="<?= url(\Altum\Router::$original_request) ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                        <?php endif ?>
                    </div>

                    <div class="dropdown-divider"></div>

                    <form action="" method="get" role="form">
                        <div class="form-group px-4">
                            <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                            <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_status" class="small"><?= l('global.status') ?></label>
                            <select name="status" id="filters_status" class="custom-select custom-select-sm">
                                <option value=""><?= l('global.all') ?></option>
                                <?php foreach(\Altum\Models\Subscription::STATUSES as $status): ?>
                                    <option value="<?= $status ?>" <?= isset($data->filters->filters['status']) && $data->filters->filters['status'] == $status ? 'selected="selected"' : null ?>><?= $format_status($status) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_plan_id" class="small"><?= l('admin_subscriptions.plan') ?></label>
                            <select name="plan_id" id="filters_plan_id" class="custom-select custom-select-sm">
                                <option value=""><?= l('global.all') ?></option>
                                <?php foreach($data->plans as $plan): ?>
                                    <option value="<?= $plan->plan_id ?>" <?= isset($data->filters->filters['plan_id']) && $data->filters->filters['plan_id'] == $plan->plan_id ? 'selected="selected"' : null ?>><?= $plan->name ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_processor" class="small"><?= l('account_billing.processor') ?></label>
                            <select name="processor" id="filters_processor" class="custom-select custom-select-sm">
                                <option value=""><?= l('global.all') ?></option>
                                <?php foreach($data->payment_processors as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= isset($data->filters->filters['processor']) && $data->filters->filters['processor'] == $key ? 'selected="selected"' : null ?>><?= l('pay.custom_plan.' . $key) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                            <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                <option value="subscription_id" <?= $data->filters->order_by == 'subscription_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="current_period_end" <?= $data->filters->order_by == 'current_period_end' ? 'selected="selected"' : null ?>><?= l('account_billing.renews_or_expires') ?></option>
                                <option value="total_amount" <?= $data->filters->order_by == 'total_amount' ? 'selected="selected"' : null ?>><?= l('account_billing.total') ?></option>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                            <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
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

<?= \Altum\Alerts::output_alerts() ?>

<div class="row mb-4">
    <div class="col-6 col-xl-2 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small"><?= l('subscription_status.active') ?></div>
                <div class="h4 mb-0"><?= nr($data->stats['active'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small"><?= l('subscription_status.past_due') ?></div>
                <div class="h4 mb-0"><?= nr($data->stats['past_due'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small"><?= l('subscription_status.paused') ?></div>
                <div class="h4 mb-0"><?= nr($data->stats['paused'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small"><?= l('subscription_status.non_renewing') ?></div>
                <div class="h4 mb-0"><?= nr($data->stats['non_renewing'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small"><?= l('admin_subscriptions.mrr') ?></div>
                <div class="h4 mb-0"><?= nr($data->mrr, 2) ?> <?= settings()->payment->default_currency ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small"><?= l('admin_subscriptions.arr') ?></div>
                <div class="h4 mb-0"><?= nr($data->arr, 2) ?> <?= settings()->payment->default_currency ?></div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive table-custom-container">
    <table class="table table-custom">
        <thead>
        <tr>
            <th><?= l('global.user') ?></th>
            <th><?= l('admin_subscriptions.plan') ?></th>
            <th><?= l('global.status') ?></th>
            <th><?= l('account_billing.price') ?></th>
            <th><?= l('account_billing.renews_or_expires') ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data->subscriptions as $row): ?>
            <?php $default_currency_mrr = $get_default_currency_mrr($row); ?>
            <tr>
                <td class="text-nowrap">
                    <div class="d-flex align-items-center">
                        <a href="<?= url('admin/user-view/' . $row->user_id) ?>">
                            <img src="<?= get_user_avatar($row->user_avatar, $row->user_email) ?>" referrerpolicy="no-referrer" loading="lazy" class="user-avatar rounded-circle mr-3" alt="" />
                        </a>
                        <div class="d-flex flex-column">
                            <a href="<?= url('admin/user-view/' . $row->user_id) ?>"><?= $row->user_name ?: l('global.unknown') ?></a>
                            <span class="text-muted small"><?= $row->user_email ?></span>
                        </div>
                    </div>
                </td>
                <td class="text-nowrap">
                    <?php if(isset($data->plans[$row->plan_id])): ?>
                        <a href="<?= url('admin/plan-update/' . $row->plan_id) ?>" class="badge badge-light"><?= $data->plans[$row->plan_id]->name ?></a>
                    <?php else: ?>
                        <span class="badge badge-light"><?= $row->plan_name ?: $row->plan_id ?></span>
                    <?php endif ?>
                </td>
                <td class="text-nowrap">
                    <span class="badge <?= $status_badges[$row->status] ?? 'badge-light' ?>"><?= $format_status($row->status) ?></span>
                </td>
                <td class="text-nowrap">
                    <div><?= nr($row->total_amount, 2) ?> <?= $row->currency ?></div>
                    <div class="text-muted small"><?= l('plan.custom_plan.' . $row->frequency) ?> / <?= $row->processor ? l('pay.custom_plan.' . $row->processor) : l('global.unknown') ?></div>
                    <?php if(in_array($row->status, $revenue_statuses)): ?>
                        <?php if($default_currency_mrr !== null): ?>
                            <div class="text-muted small"><?= nr($default_currency_mrr, 2) ?> <?= settings()->payment->default_currency ?> <?= l('admin_subscriptions.mrr_contribution') ?></div>
                        <?php elseif($row->currency && $row->currency != settings()->payment->default_currency): ?>
                            <div class="text-muted small"><?= l('admin_subscriptions.currency_conversion_missing') ?></div>
                        <?php endif ?>
                    <?php endif ?>
                </td>
                <td class="text-nowrap"><?= $row->current_period_end ? \Altum\Date::get($row->current_period_end, 2) : l('global.unknown') ?></td>
                <td class="text-nowrap">
                    <div class="d-flex justify-content-end">
                        <a href="<?= url('admin/subscriptions/view/' . $row->subscription_id) ?>" class="btn btn-sm btn-light">
                            <i class="fas fa-fw fa-sm fa-eye mr-1"></i> <?= l('global.view') ?>
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<?php if(!count($data->subscriptions)): ?>
    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
        'filters_get' => $data->filters->get ?? [],
        'name' => 'admin_subscriptions',
        'has_secondary_text' => false,
    ]); ?>
<?php endif ?>

<div class="mt-3"><?= $data->pagination ?></div>
