<?php defined('ALTUMCODE') || die() ?>

<?php
$subscription = $data->subscription;
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

$manual_period_frequencies = ['monthly', 'annual'];
$manual_period_currency = $subscription->currency ?: settings()->payment->default_currency;
$manual_period_currencies = [];
$manual_period_price_map = [];

foreach((array) settings()->payment->currencies as $currency => $currency_data) {
    $manual_period_currencies[$currency] = $currency_data;

    foreach($manual_period_frequencies as $frequency) {
        $manual_period_price_map[$frequency][$currency] = isset($data->plan->prices->{$frequency}->{$currency})
            ? (float) $data->plan->prices->{$frequency}->{$currency}
            : null;
    }
}

if(!isset($manual_period_currencies[$manual_period_currency])) {
    $manual_period_currencies[$manual_period_currency] = (object) [];
}

$can_apply_manual_period = $data->plan && $subscription->status != 'lifetime' && $subscription->frequency != 'lifetime' && !$subscription->external_subscription_id;
$manual_period_payment_datetime = (new \DateTime())->format('Y-m-d\TH:i');
?>

<div class="d-flex flex-column flex-md-row justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-fw fa-xs fa-repeat text-primary-900 mr-2"></i> <?= sprintf(l('admin_subscriptions.view.header'), '#' . $subscription->subscription_id) ?></h1>
        <div class="text-muted"><?= l('admin_subscriptions.view.subheader') ?></div>
    </div>

    <div class="d-flex flex-wrap gap-3 mt-3 mt-md-0">
        <a href="<?= url('admin/subscriptions') ?>" class="btn btn-light">
            <i class="fas fa-fw fa-sm fa-arrow-left mr-1"></i> <?= l('global.back') ?>
        </a>

        <div class="dropdown">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-fw fa-sm fa-bolt mr-1"></i> <?= l('global.actions') ?>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="<?= url('admin/subscriptions/sync_user/' . $subscription->subscription_id . '?global_token=' . \Altum\Csrf::get('global_token')) ?>" class="dropdown-item">
                    <i class="fas fa-fw fa-sm fa-sync mr-2"></i> <?= l('admin_subscriptions.action.sync_user') ?>
                </a>
                <a href="<?= url('admin/subscriptions/sync_from_user/' . $subscription->subscription_id . '?global_token=' . \Altum\Csrf::get('global_token')) ?>" class="dropdown-item">
                    <i class="fas fa-fw fa-sm fa-user-check mr-2"></i> <?= l('admin_subscriptions.action.sync_from_user') ?>
                </a>
                <a href="<?= url('admin/subscriptions/mark_past_due/' . $subscription->subscription_id . '?global_token=' . \Altum\Csrf::get('global_token')) ?>" class="dropdown-item">
                    <i class="fas fa-fw fa-sm fa-exclamation-circle mr-2"></i> <?= l('admin_subscriptions.action.mark_past_due') ?>
                </a>
                <?php if($subscription->status != 'paused'): ?>
                    <a href="<?= url('admin/subscriptions/pause/' . $subscription->subscription_id . '?global_token=' . \Altum\Csrf::get('global_token')) ?>" class="dropdown-item" onclick='return confirm(<?= json_encode(l('admin_subscriptions.confirm.pause')) ?>)'>
                        <i class="fas fa-fw fa-sm fa-pause mr-2"></i> <?= l('account_billing.pause') ?>
                    </a>
                <?php endif ?>
                <?php if(in_array($subscription->status, ['paused', 'past_due', 'non_renewing', 'canceled'])): ?>
                    <a href="<?= url('admin/subscriptions/resume/' . $subscription->subscription_id . '?global_token=' . \Altum\Csrf::get('global_token')) ?>" class="dropdown-item">
                        <i class="fas fa-fw fa-sm fa-play mr-2"></i> <?= l('account_billing.resume') ?>
                    </a>
                <?php endif ?>
                <?php if(!in_array($subscription->status, ['canceled', 'expired'])): ?>
                    <div class="dropdown-divider"></div>
                    <a href="<?= url('admin/subscriptions/cancel/' . $subscription->subscription_id . '?global_token=' . \Altum\Csrf::get('global_token')) ?>" class="dropdown-item text-danger" onclick='return confirm(<?= json_encode(l('admin_subscriptions.confirm.cancel')) ?>)'>
                        <i class="fas fa-fw fa-sm fa-ban mr-2"></i> <?= l('account_billing.cancel') ?>
                    </a>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<script>
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    let manual_period_form = null;

    document.querySelectorAll('[data-manual-period-form]').forEach(form => {
        const prices = JSON.parse(form.querySelector('[data-manual-period-prices]').textContent || '{}');
        const frequency = form.querySelector('[name="frequency"]');
        const currency = form.querySelector('[name="currency"]');
        const amount = form.querySelector('[data-manual-period-amount]');
        const submit = form.querySelector('[data-manual-period-submit]');

        const price_not_configured = <?= json_encode(l('admin_subscriptions.price_not_configured')) ?>;

        const update_amount = () => {
            const selected_frequency = frequency.value;
            const selected_currency = currency.value;
            const has_price = prices[selected_frequency]
                && prices[selected_frequency][selected_currency] !== undefined
                && prices[selected_frequency][selected_currency] !== null;

            if(!has_price) {
                amount.textContent = price_not_configured;
                submit.disabled = true;
                return;
            }

            const price = Number(prices[selected_frequency][selected_currency]);

            amount.textContent = price.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' ' + selected_currency;

            submit.disabled = Number.isNaN(price) || price < 0;
        };

        frequency.addEventListener('change', update_amount);
        currency.addEventListener('change', update_amount);
        update_amount();
    });

    document.querySelectorAll('[data-manual-period-open-confirm]').forEach(button => {
        button.addEventListener('click', () => {
            const form = button.closest('form');

            if(!form) {
                return;
            }

            if(form.reportValidity && !form.reportValidity()) {
                return;
            }

            manual_period_form = form;
            $('#manual_period_confirm_modal').modal('show');
        });
    });

    document.querySelector('[data-manual-period-confirm-submit]')?.addEventListener('click', () => {
        if(manual_period_form) {
            manual_period_form.submit();
        }
    });

    $('#invoice_paid_datetime_modal').on('show.bs.modal', event => {
        const button = event.relatedTarget;
        const modal = event.currentTarget;
        const form = modal.querySelector('[data-invoice-paid-datetime-form]');
        const invoice_id = button.getAttribute('data-invoice-id');
        const paid_datetime = button.getAttribute('data-paid-datetime');

        form.action = form.getAttribute('data-action-template').replace('INVOICE_ID', invoice_id);
        modal.querySelector('[data-invoice-paid-datetime-id]').textContent = '#' + invoice_id;
        modal.querySelector('[name="paid_datetime"]').value = paid_datetime;
    });
});
</script>

<?= \Altum\Alerts::output_alerts() ?>

<div class="row">
    <div class="col-xl-7 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="h5 mb-1"><?= l('admin_subscriptions.subscription_details') ?></h2>
                        <p class="text-muted mb-0"><?= l('admin_subscriptions.subscription_details_help') ?></p>
                    </div>
                    <span class="badge <?= $status_badges[$subscription->status] ?? 'badge-light' ?>"><?= $format_status($subscription->status) ?></span>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small"><?= l('global.user') ?></div>
                        <?php if($data->user): ?>
                            <a href="<?= url('admin/user-view/' . $data->user->user_id) ?>" class="font-weight-bold"><?= $data->user->name ?> <span class="text-muted"><?= $data->user->email ?></span></a>
                        <?php else: ?>
                            <div class="font-weight-bold"><?= l('global.unknown') ?></div>
                        <?php endif ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="text-muted small"><?= l('admin_subscriptions.plan') ?></div>
                        <div class="font-weight-bold"><?= $data->plan->name ?? $subscription->plan_id ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="text-muted small"><?= l('account_billing.price') ?></div>
                        <div class="font-weight-bold"><?= nr($subscription->total_amount, 2) ?> <?= $subscription->currency ?> / <?= l('plan.custom_plan.' . $subscription->frequency) ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="text-muted small"><?= l('account_billing.processor') ?></div>
                        <div class="font-weight-bold"><?= $subscription->processor ? l('pay.custom_plan.' . $subscription->processor) : l('global.unknown') ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="text-muted small"><?= l('account_billing.renews_or_expires') ?></div>
                        <div class="font-weight-bold"><?= $subscription->current_period_end ? \Altum\Date::get($subscription->current_period_end, 2) : l('global.unknown') ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="text-muted small"><?= l('admin_subscriptions.external_id') ?></div>
                        <div class="font-weight-bold text-truncate"><?= $subscription->external_subscription_id ?: l('global.none') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5 mb-4">
        <?php if($can_apply_manual_period): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-1"><?= l('admin_subscriptions.manual_period') ?></h2>
                    <p class="text-muted"><?= l('admin_subscriptions.manual_period_help') ?></p>

                    <form action="<?= url('admin/subscriptions/apply_manual_period/' . $subscription->subscription_id) ?>" method="post" role="form" data-manual-period-form>
                        <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />
                        <script type="application/json" data-manual-period-prices><?= json_encode($manual_period_price_map) ?></script>

                        <div class="form-group">
                            <label for="manual_period_frequency"><i class="fas fa-fw fa-sm fa-calendar-alt text-muted mr-1"></i> <?= l('pay.custom_plan.payment_frequency') ?></label>
                            <select id="manual_period_frequency" name="frequency" class="custom-select" required="required">
                                <?php foreach($manual_period_frequencies as $frequency): ?>
                                    <option value="<?= $frequency ?>" <?= $subscription->frequency == $frequency ? 'selected="selected"' : null ?>><?= l('plan.custom_plan.' . $frequency) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="manual_period_currency"><i class="fas fa-fw fa-sm fa-coins text-muted mr-1"></i> <?= l('admin_subscriptions.currency') ?></label>
                            <select id="manual_period_currency" name="currency" class="custom-select" required="required">
                                <?php foreach($manual_period_currencies as $currency => $currency_data): ?>
                                    <option value="<?= $currency ?>" <?= $manual_period_currency == $currency ? 'selected="selected"' : null ?>><?= $currency ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="manual_period_payment_datetime"><i class="fas fa-fw fa-sm fa-calendar-check text-muted mr-1"></i> <?= l('admin_subscriptions.payment_datetime') ?></label>
                            <input id="manual_period_payment_datetime" type="datetime-local" name="payment_datetime" class="form-control" value="<?= $manual_period_payment_datetime ?>" required="required" />
                            <small class="form-text text-muted"><?= l('admin_subscriptions.payment_datetime_help') ?></small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-3">
                            <span class="text-muted"><?= l('account_billing.total') ?></span>
                            <span class="font-weight-bold" data-manual-period-amount></span>
                        </div>

                        <button type="button" class="btn btn-primary btn-block" data-manual-period-submit data-manual-period-open-confirm>
                            <i class="fas fa-fw fa-sm fa-check mr-1"></i> <?= l('admin_subscriptions.manual_period_submit') ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endif ?>

        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5"><?= l('account_billing.entitlements') ?></h2>
                <p class="text-muted"><?= l('admin_subscriptions.entitlements_help') ?></p>

                <?php $entitlement_rows = 0; ?>
                <div class="list-group list-group-flush">
                    <?php foreach((array) $data->entitlements as $key => $value): ?>
                        <?php if(is_object($value) || is_array($value)) continue; ?>
                        <?php if($entitlement_rows >= 12) break; ?>
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

<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card h-100">
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
                                <th><?= l('admin_subscriptions.invoice_paid_on') ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->invoices as $invoice): ?>
                                <?php
                                $is_proforma_invoice = $invoice->type == 'proforma';
                                $invoice_status_label = $is_proforma_invoice ? l('invoice_status.proforma') : (l('invoice_status.' . $invoice->status, null, true) ?? ucfirst(str_replace('_', ' ', $invoice->status)));
                                $invoice_status_badge = $is_proforma_invoice ? 'badge-info' : ($invoice->status == 'paid' ? 'badge-success' : ($invoice->status == 'past_due' || $invoice->status == 'payment_due' ? 'badge-danger' : 'badge-light'));
                                $invoice_paid_datetime = $invoice->paid_datetime ?: $invoice->datetime;
                                try {
                                    $invoice_paid_datetime_input = (new \DateTime($invoice_paid_datetime))->format('Y-m-d\TH:i');
                                } catch(\Exception $exception) {
                                    $invoice_paid_datetime_input = '';
                                }
                                ?>
                                <tr>
                                    <td class="text-nowrap">#<?= $invoice->invoice_id ?></td>
                                    <td class="text-nowrap">
                                        <span class="badge <?= $invoice_status_badge ?>"><?= $invoice_status_label ?></span>
                                    </td>
                                    <td class="text-nowrap"><?= nr($invoice->total_amount, 2) ?> <?= $invoice->currency ?></td>
                                    <td class="text-nowrap">
                                        <?= \Altum\Date::get($invoice_paid_datetime, 2) ?>
                                        <?php if($is_proforma_invoice && $invoice->due_datetime): ?>
                                            <div class="text-muted small"><?= l('invoice.due_date') ?>: <?= \Altum\Date::get($invoice->due_datetime, 2) ?></div>
                                        <?php endif ?>
                                        <?php if($invoice->status == 'paid'): ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light ml-2"
                                                data-toggle="modal"
                                                data-target="#invoice_paid_datetime_modal"
                                                data-invoice-id="<?= $invoice->invoice_id ?>"
                                                data-paid-datetime="<?= input_clean($invoice_paid_datetime_input) ?>"
                                                data-tooltip
                                                title="<?= l('admin_subscriptions.edit_invoice_paid_datetime') ?>"
                                            >
                                                <i class="fas fa-fw fa-sm fa-pen"></i>
                                            </button>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-nowrap text-right">
                                        <?php if($invoice->payment_id && settings()->payment->invoice_is_enabled): ?>
                                            <a href="<?= url('admin/invoice/' . $invoice->payment_id) ?>" target="_blank" class="btn btn-sm btn-light">
                                                <i class="fas fa-fw fa-sm fa-file-invoice mr-1"></i> <?= l('global.view') ?>
                                            </a>
                                        <?php elseif($is_proforma_invoice && settings()->payment->invoice_is_enabled): ?>
                                            <a href="<?= url('admin/invoice/proforma/' . $invoice->invoice_id) ?>" target="_blank" class="btn btn-sm btn-light">
                                                <i class="fas fa-fw fa-sm fa-file-invoice mr-1"></i> <?= l('global.view') ?>
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
    </div>

    <div class="col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5 mb-3"><?= l('admin_subscriptions.items') ?></h2>

                <?php if(count($data->items)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($data->items as $item): ?>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div class="text-truncate mr-3">
                                    <div class="font-weight-bold"><?= $item->name ?: $item->item_id ?></div>
                                    <div class="text-muted small"><?= $item->item_type ?> x <?= nr($item->quantity) ?></div>
                                </div>
                                <span class="badge badge-light"><?= nr($item->total_amount, 2) ?> <?= $item->currency ?></span>
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

<div class="modal fade" id="manual_period_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-check text-primary-900 mr-2"></i>
                        <?= l('admin_subscriptions.manual_period') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted"><?= l('admin_subscriptions.confirm.manual_period') ?></p>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-light mr-2" data-dismiss="modal"><?= l('global.cancel') ?></button>
                    <button type="button" class="btn btn-primary" data-manual-period-confirm-submit>
                        <i class="fas fa-fw fa-sm fa-check mr-1"></i> <?= l('admin_subscriptions.manual_period_submit') ?>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="invoice_paid_datetime_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form action="" method="post" role="form" data-invoice-paid-datetime-form data-action-template="<?= url('admin/subscriptions/update_invoice_paid_datetime/INVOICE_ID') ?>">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="modal-title">
                            <i class="fas fa-fw fa-sm fa-calendar-check text-primary-900 mr-2"></i>
                            <?= l('admin_subscriptions.edit_invoice_paid_datetime') ?> <span data-invoice-paid-datetime-id></span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <p class="text-muted"><?= l('admin_subscriptions.edit_invoice_paid_datetime_help') ?></p>

                    <div class="form-group">
                        <label for="invoice_paid_datetime"><i class="fas fa-fw fa-sm fa-calendar-check text-muted mr-1"></i> <?= l('admin_subscriptions.payment_datetime') ?></label>
                        <input id="invoice_paid_datetime" type="datetime-local" name="paid_datetime" class="form-control" required="required" />
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-light mr-2" data-dismiss="modal"><?= l('global.cancel') ?></button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-fw fa-sm fa-save mr-1"></i> <?= l('global.update') ?>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5 mb-3"><?= l('account_billing.billing_timeline') ?></h2>

                <?php if(count($data->events)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($data->events as $event): ?>
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

    <div class="col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5 mb-3"><?= l('admin_subscriptions.dunning_attempts') ?></h2>

                <?php if(count($data->dunning_attempts)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('global.id') ?></th>
                                <th><?= l('global.status') ?></th>
                                <th><?= l('admin_subscriptions.scheduled') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dunning_attempts as $attempt): ?>
                                <tr>
                                    <td>#<?= $attempt->attempt_number ?></td>
                                    <td><span class="badge badge-light"><?= ucfirst($attempt->status) ?></span></td>
                                    <td><?= \Altum\Date::get($attempt->scheduled_datetime, 2) ?></td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0"><?= l('global.no_data') ?></p>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
