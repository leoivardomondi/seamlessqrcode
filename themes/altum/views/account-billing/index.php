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

$format_money = function($amount, $currency) {
    return nr($amount, 2) . ' ' . $currency;
};
?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['account_header_menu'] ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><?= l('account_billing.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('account_billing.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3">
            <a href="<?= url('plan/upgrade') ?>" class="btn btn-outline-primary">
                <i class="fas fa-fw fa-sm fa-arrow-up mr-1"></i> <?= l('account_billing.change_plan') ?>
            </a>
            <a href="<?= url('account-payments') ?>" class="btn btn-light">
                <i class="fas fa-fw fa-sm fa-receipt mr-1"></i> <?= l('account_payments.menu') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="h5 mb-1"><?= l('account_billing.current_subscription') ?></h2>
                            <p class="text-muted mb-0"><?= l('account_billing.current_subscription_help') ?></p>
                        </div>

                        <?php if($data->active_subscription): ?>
                            <span class="badge <?= $status_badges[$data->active_subscription->status] ?? 'badge-light' ?>">
                                <?= $format_status($data->active_subscription->status) ?>
                            </span>
                        <?php endif ?>
                    </div>

                    <?php if($data->active_subscription): ?>
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <div class="text-muted small"><?= l('account_billing.plan') ?></div>
                                <div class="font-weight-bold"><?= ($this->user->plan->translations->{\Altum\Language::$name}->name ?? '') ?: $this->user->plan->name ?></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="text-muted small"><?= l('account_billing.price') ?></div>
                                <div class="font-weight-bold">
                                    <?= $format_money($data->active_subscription->total_amount, $data->active_subscription->currency) ?>
                                    <span class="text-muted font-weight-normal">/ <?= l('plan.custom_plan.' . $data->active_subscription->frequency) ?></span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="text-muted small"><?= l('account_billing.renews_or_expires') ?></div>
                                <div class="font-weight-bold"><?= $data->active_subscription->current_period_end ? \Altum\Date::get($data->active_subscription->current_period_end, 2) : l('global.unknown') ?></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="text-muted small"><?= l('account_billing.processor') ?></div>
                                <div class="font-weight-bold">
                                    <?php if($data->active_subscription->processor && isset($data->payment_processors[$data->active_subscription->processor])): ?>
                                        <i class="<?= $data->payment_processors[$data->active_subscription->processor]['icon'] ?> fa-fw mr-1" style="color: <?= $data->payment_processors[$data->active_subscription->processor]['color'] ?>"></i>
                                        <?= l('pay.custom_plan.' . $data->active_subscription->processor) ?>
                                    <?php else: ?>
                                        <?= l('global.unknown') ?>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <?php if(in_array($data->active_subscription->status, ['active', 'trialing', 'past_due', 'non_renewing'])): ?>
                                <a href="<?= url('account-billing/pause_subscription/' . $data->active_subscription->subscription_id . '?token=' . \Altum\Csrf::get()) ?>" class="btn btn-sm btn-light" onclick='return confirm(<?= json_encode(l('account_billing.pause_confirm')) ?>)'>
                                    <i class="fas fa-fw fa-sm fa-pause mr-1"></i> <?= l('account_billing.pause') ?>
                                </a>
                            <?php endif ?>

                            <?php if(in_array($data->active_subscription->status, ['paused', 'past_due', 'non_renewing'])): ?>
                                <a href="<?= url('account-billing/resume_subscription/' . $data->active_subscription->subscription_id . '?token=' . \Altum\Csrf::get()) ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-fw fa-sm fa-play mr-1"></i> <?= l('account_billing.resume') ?>
                                </a>
                            <?php endif ?>

                            <?php if(!in_array($data->active_subscription->status, ['canceled', 'expired', 'lifetime'])): ?>
                                <a href="<?= url('account-billing/cancel_subscription/' . $data->active_subscription->subscription_id . '?token=' . \Altum\Csrf::get()) ?>" class="btn btn-sm btn-outline-secondary" onclick='return confirm(<?= json_encode(l('account_billing.cancel_confirm')) ?>)'>
                                    <i class="fas fa-fw fa-sm fa-ban mr-1"></i> <?= l('account_billing.cancel') ?>
                                </a>
                            <?php endif ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-4">
                            <p class="text-muted"><?= l('account_billing.no_subscription') ?></p>
                            <a href="<?= url('plan/upgrade') ?>" class="btn btn-primary">
                                <i class="fas fa-fw fa-sm fa-arrow-up mr-1"></i> <?= l('account_billing.choose_plan') ?>
                            </a>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <div class="col-xl-5 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5"><?= l('account_billing.entitlements') ?></h2>
                    <p class="text-muted"><?= l('account_billing.entitlements_help') ?></p>

                    <?php $entitlement_rows = 0; ?>
                    <div class="list-group list-group-flush">
                        <?php foreach((array) $data->entitlements as $key => $value): ?>
                            <?php if(is_object($value) || is_array($value)) continue; ?>
                            <?php if($entitlement_rows >= 8) break; ?>
                            <?php $entitlement_rows++; ?>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-truncate mr-3"><?= input_clean(str_replace('_', ' ', $key)) ?></span>
                                <span class="badge badge-light"><?= is_bool($value) ? ($value ? l('global.yes') : l('global.no')) : (is_numeric($value) ? nr($value) : input_clean($value)) ?></span>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <?php if(!$entitlement_rows): ?>
                        <p class="text-muted mb-0"><?= l('global.no_data') ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3"><?= l('account_billing.invoices') ?></h2>

            <?php if(count($data->invoices)): ?>
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <thead>
                        <tr>
                            <th><?= l('account_billing.invoice') ?></th>
                            <th><?= l('global.status') ?></th>
                            <th><?= l('account_billing.total') ?></th>
                            <th><?= l('global.datetime') ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($data->invoices as $invoice): ?>
                            <?php
                            $is_proforma_invoice = $invoice->type == 'proforma';
                            $invoice_status_label = $is_proforma_invoice ? l('invoice_status.proforma') : (l('invoice_status.' . $invoice->status, null, true) ?? ucfirst(str_replace('_', ' ', $invoice->status)));
                            $invoice_status_badge = $is_proforma_invoice ? 'badge-info' : ($invoice->status == 'paid' ? 'badge-success' : ($invoice->status == 'past_due' || $invoice->status == 'payment_due' ? 'badge-danger' : 'badge-light'));
                            ?>
                            <tr>
                                <td class="text-nowrap">#<?= $invoice->invoice_id ?></td>
                                <td class="text-nowrap">
                                    <span class="badge <?= $invoice_status_badge ?>"><?= $invoice_status_label ?></span>
                                </td>
                                <td class="text-nowrap"><?= $format_money($invoice->total_amount, $invoice->currency) ?></td>
                                <td class="text-nowrap">
                                    <?= \Altum\Date::get($invoice->datetime, 2) ?>
                                    <?php if($is_proforma_invoice && $invoice->due_datetime): ?>
                                        <div class="text-muted small"><?= l('invoice.due_date') ?>: <?= \Altum\Date::get($invoice->due_datetime, 2) ?></div>
                                    <?php endif ?>
                                </td>
                                <td class="text-nowrap text-right">
                                    <?php if($invoice->payment_id && settings()->payment->invoice_is_enabled): ?>
                                        <a href="<?= url('invoice/' . $invoice->payment_id) ?>" target="_blank" class="btn btn-sm btn-light">
                                            <i class="fas fa-fw fa-sm fa-file-invoice mr-1"></i> <?= l('account_payments.invoice') ?>
                                        </a>
                                    <?php elseif($is_proforma_invoice && settings()->payment->invoice_is_enabled): ?>
                                        <a href="<?= url('invoice/proforma/' . $invoice->invoice_id) ?>" target="_blank" class="btn btn-sm btn-light">
                                            <i class="fas fa-fw fa-sm fa-file-invoice mr-1"></i> <?= l('invoice.proforma_invoice') ?>
                                        </a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0"><?= l('account_billing.no_invoices') ?></p>
            <?php endif ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3"><?= l('account_billing.subscription_history') ?></h2>

                    <?php if(count($data->subscriptions)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($data->subscriptions as $subscription): ?>
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div class="text-truncate mr-3">
                                        <div class="font-weight-bold text-truncate"><?= l('plan.custom_plan.' . $subscription->frequency) ?></div>
                                        <div class="text-muted small"><?= \Altum\Date::get($subscription->datetime, 2) ?></div>
                                    </div>
                                    <span class="badge <?= $status_badges[$subscription->status] ?? 'badge-light' ?>"><?= $format_status($subscription->status) ?></span>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0"><?= l('global.no_data') ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3"><?= l('account_billing.billing_timeline') ?></h2>

                    <?php if(count($data->billing_events)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($data->billing_events as $event): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <span class="font-weight-bold"><?= l('billing_event.' . $event->event_type, null, true) ?? $event->event_type ?></span>
                                        <span class="text-muted small"><?= \Altum\Date::get($event->datetime, 2) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= $event->source ?></div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0"><?= l('global.no_data') ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>
