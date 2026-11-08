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

$manual_period_frequencies = ['monthly', 'quarterly', 'biannual', 'annual'];
$manual_period_currency = $subscription->currency ?: settings()->payment->default_currency;
$manual_period_currencies = [];
$manual_period_price_map = [];

foreach((array) settings()->payment->currencies as $currency => $currency_data) {
    $manual_period_currencies[$currency] = $currency_data;
    $monthly_price = isset($data->plan->prices->monthly->{$currency}) ? (float) $data->plan->prices->monthly->{$currency} : null;

    foreach($manual_period_frequencies as $frequency) {
        if(isset($data->plan->prices->{$frequency}->{$currency})) {
            $manual_period_price_map[$frequency][$currency] = (float) $data->plan->prices->{$frequency}->{$currency};
        } else if($monthly_price !== null) {
            $multiplier = match($frequency) {
                'quarterly' => 3,
                'biannual' => 6,
                'annual' => 12,
                default => 1,
            };
            $manual_period_price_map[$frequency][$currency] = (float) ($monthly_price * $multiplier);
        } else {
            $manual_period_price_map[$frequency][$currency] = null;
        }
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
        const confirmation_message = form.querySelector('[name="confirmation_message"]');
        const code_input = form.querySelector('[name="code"]');
        const payment_datetime_input = form.querySelector('[name="payment_datetime"]');
        const start_datetime_input = form.querySelector('[name="start_datetime"]');
        const end_datetime_input = form.querySelector('[name="end_datetime"]');
        const next_renewal_preview = form.querySelector('#manual_period_next_renewal_preview');
        const extraction_badge = form.querySelector('#manual_period_extraction_badge');

        const price_not_configured = <?= json_encode(l('admin_subscriptions.price_not_configured')) ?>;

        const pad = n => String(n).padStart(2, '0');

        const calculate_next_renewal = () => {
            if(!start_datetime_input || !next_renewal_preview) return;

            const start_val = start_datetime_input.value;
            if(!start_val) {
                next_renewal_preview.textContent = '-';
                if(end_datetime_input) end_datetime_input.value = '';
                return;
            }

            const start_date = new Date(start_val);
            if(isNaN(start_date.getTime())) {
                next_renewal_preview.textContent = '-';
                if(end_datetime_input) end_datetime_input.value = '';
                return;
            }

            const selected_frequency = frequency.value;
            let end_date = new Date(start_date.getTime());

            if(selected_frequency === 'monthly') {
                end_date.setMonth(end_date.getMonth() + 1);
            } else if(selected_frequency === 'quarterly') {
                end_date.setMonth(end_date.getMonth() + 3);
            } else if(selected_frequency === 'biannual') {
                end_date.setMonth(end_date.getMonth() + 6);
            } else if(selected_frequency === 'annual') {
                end_date.setFullYear(end_date.getFullYear() + 1);
            }

            const iso_end = `${end_date.getFullYear()}-${pad(end_date.getMonth() + 1)}-${pad(end_date.getDate())} ${pad(end_date.getHours())}:${pad(end_date.getMinutes())}:00`;
            if(end_datetime_input) {
                end_datetime_input.value = iso_end;
            }

            const options = { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };
            next_renewal_preview.textContent = end_date.toLocaleDateString(undefined, options);
        };

        const parse_message = (text) => {
            if(!text || !text.trim()) return null;
            let code = null, amount_val = null, datetime_iso = null, datetime_display = null;

            // 1. Transaction Code (M-PESA / Bank / Airtel)
            const codeMatch1 = text.match(/\b([A-Z0-9]{10})\s+(?:Confirmed|confirmed)/i);
            const codeMatch2 = text.match(/(?:Trans(?:action)?\s*(?:ID|Id|id|No|Ref)?|Ref(?:erence)?)\s*[:#.]?\s*([A-Z0-9]{6,20})/i);
            const codeMatch3 = text.match(/\b([A-Z]{2}[0-9A-Z]{8})\b/i);
            const codeMatch4 = text.match(/^([A-Z0-9]{8,14})\b/i);

            if(codeMatch1) code = codeMatch1[1].toUpperCase();
            else if(codeMatch2) code = codeMatch2[1].toUpperCase();
            else if(codeMatch3) code = codeMatch3[1].toUpperCase();
            else if(codeMatch4) code = codeMatch4[1].toUpperCase();

            // 2. Amount
            const amtMatch = text.match(/(?:Ksh|KES|\$|USD)\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?|[0-9]+(?:\.[0-9]{2})?)/i)
                || text.match(/(?:sent|paid|received|transferred)\s*(?:to|from)?\s*(?:Ksh|KES|\$)?\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?|[0-9]+(?:\.[0-9]{2})?)/i);
            if(amtMatch) {
                amount_val = parseFloat(amtMatch[1].replace(/,/g, ''));
            }

            // 3. Date & Time
            const dtMatch = text.match(/(?:on|On|dated|date:?)\s*(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})(?:\s+at|\s*,|\s+)\s*(\d{1,2}:\d{2}(?::\d{2})?\s*(?:AM|PM|am|pm)?)/i)
                || text.match(/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})\s+at\s+(\d{1,2}:\d{2}(?::\d{2})?\s*(?:AM|PM|am|pm)?)/i);

            if(dtMatch) {
                let day = parseInt(dtMatch[1], 10);
                let month = parseInt(dtMatch[2], 10);
                let year = parseInt(dtMatch[3], 10);
                let timeStr = dtMatch[4];

                if(year < 100) year += 2000;
                if(month > 12 && day <= 12) {
                    const tmp = day;
                    day = month;
                    month = tmp;
                }

                let hours = 0, minutes = 0;
                const timeMatch = timeStr.match(/(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM)?/i);
                if(timeMatch) {
                    hours = parseInt(timeMatch[1], 10);
                    minutes = parseInt(timeMatch[2], 10);
                    const meridiem = (timeMatch[3] || '').toUpperCase();
                    if(meridiem === 'PM' && hours < 12) hours += 12;
                    if(meridiem === 'AM' && hours === 12) hours = 0;
                }

                datetime_iso = `${year}-${pad(month)}-${pad(day)}T${pad(hours)}:${pad(minutes)}`;
                datetime_display = `${pad(day)}/${pad(month)}/${year} ${timeStr}`;
            }

            if(code || amount_val !== null || datetime_iso) {
                return { code, amount: amount_val, datetime_iso, datetime_display };
            }
            return null;
        };

        if(confirmation_message) {
            const handle_confirmation_input = () => {
                const extracted = parse_message(confirmation_message.value);
                if(extracted) {
                    if(extracted.code && code_input) {
                        code_input.value = extracted.code;
                    }
                    if(extracted.datetime_iso) {
                        if(payment_datetime_input) payment_datetime_input.value = extracted.datetime_iso;
                        if(start_datetime_input) start_datetime_input.value = extracted.datetime_iso;
                        calculate_next_renewal();
                    }
                    if(extraction_badge) {
                        extraction_badge.innerHTML = `<div class="alert alert-success py-2 px-3 small mb-0"><i class="fas fa-check-circle mr-1"></i> <strong>Extracted from message:</strong> ${extracted.code ? 'Code: <code>' + extracted.code + '</code> ' : ''}${extracted.amount ? ' | Amount: <strong>' + extracted.amount.toLocaleString(undefined, {minimumFractionDigits: 2}) + '</strong>' : ''}${extracted.datetime_display ? ' | Date: <strong>' + extracted.datetime_display + '</strong>' : ''}</div>`;
                        extraction_badge.style.display = 'block';
                    }
                } else if(extraction_badge) {
                    extraction_badge.style.display = 'none';
                    extraction_badge.innerHTML = '';
                }
            };

            confirmation_message.addEventListener('input', handle_confirmation_input);
            confirmation_message.addEventListener('paste', () => setTimeout(handle_confirmation_input, 50));
        }

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
            calculate_next_renewal();
        };

        frequency.addEventListener('change', () => {
            update_amount();
            calculate_next_renewal();
        });
        currency.addEventListener('change', update_amount);
        if(start_datetime_input) {
            start_datetime_input.addEventListener('change', calculate_next_renewal);
            start_datetime_input.addEventListener('input', calculate_next_renewal);
        }

        update_amount();
        calculate_next_renewal();
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

            const modal = document.querySelector('#manual_period_confirm_modal');
            if(modal) {
                const code_val = form.querySelector('[name="code"]')?.value || 'N/A';
                const freq_elem = form.querySelector('[name="frequency"]');
                const freq_text = freq_elem ? freq_elem.options[freq_elem.selectedIndex].text : '';
                const start_val = form.querySelector('[name="start_datetime"]')?.value || '';
                const end_text = form.querySelector('#manual_period_next_renewal_preview')?.textContent || '';
                const amt_text = form.querySelector('[data-manual-period-amount]')?.textContent || '';

                const summary_code = modal.querySelector('[data-summary-code]');
                const summary_freq = modal.querySelector('[data-summary-frequency]');
                const summary_start = modal.querySelector('[data-summary-start]');
                const summary_end = modal.querySelector('[data-summary-end]');
                const summary_amount = modal.querySelector('[data-summary-amount]');

                if(summary_code) summary_code.textContent = code_val;
                if(summary_freq) summary_freq.textContent = freq_text;
                if(summary_start) summary_start.textContent = start_val ? start_val.replace('T', ' ') : '-';
                if(summary_end) summary_end.textContent = end_text;
                if(summary_amount) summary_amount.textContent = amt_text;
            }

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
                            <label for="manual_period_confirmation_message"><i class="fas fa-fw fa-sm fa-receipt text-muted mr-1"></i> Payment confirmation message (M-PESA / Bank)</label>
                            <textarea id="manual_period_confirmation_message" name="confirmation_message" class="form-control" rows="3" placeholder="Paste M-PESA paybill or bank confirmation message here (e.g. SLD3821K9L Confirmed. Ksh2,600.00 sent to CO-OPERATIVE BANK OF KENYA on 8/11/26 at 8:37 PM)..."></textarea>
                            <small class="form-text text-muted">Paste the confirmation message to automatically extract transaction code, payment date & time, and amount.</small>
                            <div id="manual_period_extraction_badge" class="mt-2" style="display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label for="manual_period_code"><i class="fas fa-fw fa-sm fa-barcode text-muted mr-1"></i> Transaction code / Reference</label>
                            <input id="manual_period_code" type="text" name="code" class="form-control" placeholder="e.g. SLD3821K9L" />
                        </div>

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

                        <div class="form-group">
                            <label for="manual_period_start_datetime"><i class="fas fa-fw fa-sm fa-calendar-plus text-muted mr-1"></i> Subscription start date</label>
                            <input id="manual_period_start_datetime" type="datetime-local" name="start_datetime" class="form-control" value="<?= $manual_period_payment_datetime ?>" required="required" />
                            <small class="form-text text-muted">Date the renewed subscription period begins (defaults to payment date).</small>
                        </div>

                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="fas fa-fw fa-sm fa-clock mr-1"></i> Next renewable date:</span>
                                <span class="font-weight-bold text-primary" id="manual_period_next_renewal_preview">-</span>
                            </div>
                            <input type="hidden" name="end_datetime" id="manual_period_end_datetime" value="" />
                            <small class="text-muted d-block mt-1">Calculated automatically based on start date and frequency.</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-3">
                            <span class="text-muted"><?= l('account_billing.total') ?></span>
                            <span class="font-weight-bold text-success h5 mb-0" data-manual-period-amount></span>
                        </div>

                        <button type="button" class="btn btn-primary btn-block" data-manual-period-submit data-manual-period-open-confirm>
                            <i class="fas fa-fw fa-sm fa-check mr-1"></i> Record payment & mark as paid
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

                <div class="border rounded p-3 mb-3 bg-light small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Transaction Code:</span>
                        <span class="font-weight-bold" data-summary-code>-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Frequency:</span>
                        <span class="font-weight-bold" data-summary-frequency>-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subscription Start:</span>
                        <span class="font-weight-bold" data-summary-start>-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Next Renewable Date:</span>
                        <span class="font-weight-bold text-primary" data-summary-end>-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total:</span>
                        <span class="font-weight-bold text-success" data-summary-amount>-</span>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-light mr-2" data-dismiss="modal"><?= l('global.cancel') ?></button>
                    <button type="button" class="btn btn-primary" data-manual-period-confirm-submit>
                        <i class="fas fa-fw fa-sm fa-check mr-1"></i> Record payment & mark as paid
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
