<?php defined('ALTUMCODE') || die() ?>

<?php
$is_payment_paid = (int) $data->payment->status === 1 && empty($data->payment->is_proforma);
$is_proforma_invoice = !empty($data->payment->is_proforma);
$invoice_heading = $is_proforma_invoice ? l('invoice.proforma_invoice') : l('invoice.invoice');
$invoice_number = $data->payment->invoice_number ?? $data->payment->id;
$download_invoice_url = $is_proforma_invoice ? url('invoice/download-proforma/' . $data->payment->id) : url('invoice/download/' . $data->payment->id);
$invoice_logo_url = settings()->main->logo_light
    ? (settings()->main->logo_light_full_url ?? \Altum\Uploads::get_full_url('logo_light') . settings()->main->logo_light)
    : (settings()->main->logo_dark ? (settings()->main->logo_dark_full_url ?? \Altum\Uploads::get_full_url('logo_dark') . settings()->main->logo_dark) : null);
$format_invoice_long_date = function($date) {
    try {
        $timezone = \Altum\Date::$timezone ?: date_default_timezone_get();
        return (new \DateTime($date))->setTimezone(new \DateTimeZone($timezone))->format('l, F jS, Y');
    } catch(\Exception $exception) {
        return null;
    }
};
$invoice_period_range = null;

if(!empty($data->payment->period_start) && !empty($data->payment->period_end)) {
    try {
        $timezone = \Altum\Date::$timezone ?: date_default_timezone_get();
        $period_start = (new \DateTime($data->payment->period_start))->setTimezone(new \DateTimeZone($timezone));
        $period_end = (new \DateTime($data->payment->period_end))->setTimezone(new \DateTimeZone($timezone))->modify('-1 day');
        $invoice_period_range = '(' . $period_start->format('d/m/Y') . ' - ' . $period_end->format('d/m/Y') . ')';
    } catch(\Exception $exception) {
        $invoice_period_range = null;
    }
}
?>

<style>
    .invoice-document {
        background: #ffffff !important;
        color: #111827 !important;
    }

    .invoice-document .text-muted {
        color: #64748b !important;
    }

    .invoice-document .invoice-logo {
        max-height: 58px;
        width: auto;
        object-fit: contain;
    }

    .invoice-document .invoice-table th,
    .invoice-document .invoice-table td {
        border-color: #e5e7eb !important;
    }

    .invoice-document .invoice-table thead th {
        background: #f8fafc !important;
        color: #475569 !important;
    }

    .invoice-paid-stamp {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #2fb344;
        color: #2fb344;
        border-radius: .35rem;
        padding: .2rem .7rem;
        font-size: .85rem;
        font-weight: 800;
        letter-spacing: .08em;
        line-height: 1.2;
        text-transform: uppercase;
        transform: rotate(-6deg);
    }

    @media print {
        body {
            background: #ffffff !important;
        }

        .invoice-document {
            box-shadow: none !important;
        }

        .invoice-document,
        .invoice-document .invoice-table thead th,
        .invoice-paid-stamp {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="container my-5 d-flex justify-content-center">
    <div class="col-lg-10">
        <div class="d-print-none d-flex justify-content-between mb-5">
            <div></div>
            <div class="d-flex align-items-center">
                <a href="<?= $download_invoice_url ?>" class="btn btn-primary mr-2"><i class="fas fa-fw fa-sm fa-download mr-1"></i> <?= l('invoice.download_pdf') ?></a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-fw fa-sm fa-print mr-1"></i> <?= l('invoice.print') ?></button>
            </div>
        </div>

        <div class="card invoice-document border-0">
            <div class="card-body p-5">

                <div class="row">
                    <div class="col-12 col-md-7 mb-4 mb-md-0">
                        <?php if($invoice_logo_url): ?>
                            <img src="<?= $invoice_logo_url ?>" class="img-fluid invoice-logo" alt="<?= l('global.accessibility.logo_alt') ?>" />
                        <?php else: ?>
                            <h1 class="h3"><?= $data->payment->business->brand_name ?? settings()->business->brand_name ?></h1>
                        <?php endif ?>
                    </div>

                    <div class="col-12 col-md-5">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="h5 mb-0"><?= $invoice_heading ?> #<?= $data->payment->business->invoice_nr_prefix . $invoice_number ?></h2>

                            <?php if($is_payment_paid): ?>
                                <span class="invoice-paid-stamp"><?= mb_strtoupper(l('invoice_status.paid')) ?></span>
                            <?php endif ?>
                        </div>

                        <table class="invoice-table">
                            <tbody>
                            <tr>
                                <td class="font-weight-bold text-muted pr-3"><?= l('invoice.invoice_nr') ?>:</td>
                                <td><?= $data->payment->business->invoice_nr_prefix . $invoice_number ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-muted pr-3"><?= l('invoice.invoice_date') ?>:</td>
                                <td><?= $format_invoice_long_date($data->payment->datetime) ?: \Altum\Date::get($data->payment->datetime, 1) ?></td>
                            </tr>
                            <?php if($is_proforma_invoice && !empty($data->payment->due_datetime)): ?>
                                <tr>
                                    <td class="font-weight-bold text-muted pr-3"><?= l('invoice.due_date') ?>:</td>
                                    <td><?= $format_invoice_long_date($data->payment->due_datetime) ?: \Altum\Date::get($data->payment->due_datetime, 1) ?></td>
                                </tr>
                            <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="row">
                        <div class="col-12 col-md-7 mb-6 mb-md-0">
                            <h3 class="h5"><?= l('invoice.vendor') ?></h3>

                            <table class="invoice-table">
                                <tbody>
                                <tr>
                                    <td class="font-weight-bold text-muted pr-3"><?= l('invoice.name') ?>:</td>
                                    <td><?= $data->payment->business->name ?></td>
                                </tr>

                                <?php if(!empty($data->payment->business->address)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= l('invoice.address') ?>:</td>
                                        <td><?= $data->payment->business->address ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->city)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= l('global.city') ?>:</td>
                                        <td><?= $data->payment->business->city ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->county)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= l('invoice.county') ?>:</td>
                                        <td><?= $data->payment->business->county ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->zip)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= l('invoice.zip') ?>:</td>
                                        <td><?= $data->payment->business->zip ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->country)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= l('global.country') ?>:</td>
                                        <td><?= get_countries_array()[$data->payment->business->country] ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->email)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= l('global.email') ?>:</td>
                                        <td><?= $data->payment->business->email ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->phone)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= l('invoice.phone') ?>:</td>
                                        <td><?= $data->payment->business->phone ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->tax_type) && !empty($data->payment->business->tax_id)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= $data->payment->business->tax_type ?>:</td>
                                        <td><?= $data->payment->business->tax_id ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->custom_key_one) && !empty($data->payment->business->custom_value_one)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= $data->payment->business->custom_key_one ?>:</td>
                                        <td><?= $data->payment->business->custom_value_one ?></td>
                                    </tr>
                                <?php endif ?>

                                <?php if(!empty($data->payment->business->custom_key_two) && !empty($data->payment->business->custom_value_two)): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= $data->payment->business->custom_key_two ?>:</td>
                                        <td><?= $data->payment->business->custom_value_two ?></td>
                                    </tr>
                                <?php endif ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-12 col-md-5">
                            <h3 class="h5"><?= l('invoice.customer') ?></h3>

                            <table class="invoice-table">
                                <tbody>
                                <?php if($data->payment->billing): ?>

                                    <tr>
                                        <td class="font-weight-bold text-muted pr-3"><?= l('invoice.name') ?>:</td>
                                        <td><?= $data->payment->billing->name ?></td>
                                    </tr>

                                    <?php if(!empty($data->payment->billing->address)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('invoice.address') ?>:</td>
                                            <td><?= $data->payment->billing->address ?></td>
                                        </tr>
                                    <?php endif ?>

                                    <?php if(!empty($data->payment->billing->city)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('global.city') ?>:</td>
                                            <td><?= $data->payment->billing->city ?></td>
                                        </tr>
                                    <?php endif ?>

                                    <?php if(!empty($data->payment->billing->county)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('invoice.county') ?>:</td>
                                            <td><?= $data->payment->billing->county ?></td>
                                        </tr>
                                    <?php endif ?>

                                    <?php if(!empty($data->payment->billing->zip)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('invoice.zip') ?>:</td>
                                            <td><?= $data->payment->billing->zip ?></td>
                                        </tr>
                                    <?php endif ?>

                                    <?php if(!empty($data->payment->billing->country)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('global.country') ?>:</td>
                                            <td><?= get_countries_array()[$data->payment->billing->country] ?></td>
                                        </tr>
                                    <?php endif ?>

                                    <?php if(!empty($data->payment->billing->email)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('global.email') ?>:</td>
                                            <td><?= $data->payment->billing->email ?></td>
                                        </tr>
                                    <?php endif ?>

                                    <?php if(!empty($data->payment->billing->phone)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('invoice.phone') ?>:</td>
                                            <td><?= $data->payment->billing->phone ?></td>
                                        </tr>
                                    <?php endif ?>

                                    <?php if($data->payment->billing->type == 'business'): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= !empty($data->payment->business->tax_type) ? $data->payment->business->tax_type : l('invoice.tax_id') ?>:</td>
                                            <td><?= $data->payment->billing->tax_id ?></td>
                                        </tr>
                                    <?php endif ?>

                                <?php else: ?>

                                    <?php if(!empty($data->payment->name)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('invoice.name') ?>:</td>
                                            <td><?= $data->payment->name ?></td>
                                        </tr>
                                    <?php endif ?>

                                    <?php if(!empty($data->payment->email)): ?>
                                        <tr>
                                            <td class="font-weight-bold text-muted pr-3"><?= l('global.email') ?>:</td>
                                            <td><?= $data->payment->email ?></td>
                                        </tr>
                                    <?php endif ?>

                                <?php endif ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <table class="table invoice-table">
                        <thead>
                        <tr>
                            <th><?= l('invoice.item') ?></th>
                            <th class="text-right"><?= l('invoice.amount') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span><?= sprintf(l('invoice.plan'), $data->payment->plan->name ?? $data->payment->plan_db->name) ?></span>
                                    <span class="text-muted"><?= sprintf(l('invoice.frequency'), l('invoice.frequency.' . $data->payment->frequency)) ?></span>
                                    <?php if($invoice_period_range): ?>
                                        <span class="text-muted"><?= $invoice_period_range ?></span>
                                    <?php endif ?>
                                </div>
                            </td>
                            <td class="text-right"><?= nr($data->payment->base_amount ? $data->payment->base_amount : $data->payment->total_amount, 2) . ' ' . $data->payment->currency ?></td>
                        </tr>

                        <?php if($data->payment->discount_amount): ?>
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span><?= l('invoice.code') ?></span>
                                        <span class="text-muted"><?= sprintf(l('invoice.code_help'), $data->payment->code) ?></span>
                                    </div>
                                </td>
                                <td class="text-right"><?= '-' . nr($data->payment->discount_amount, 2) . ' ' . $data->payment->currency ?></td>
                            </tr>
                        <?php endif ?>

                        <?php if(!empty($data->payment_taxes)): ?>
                            <?php foreach($data->payment_taxes as $row): ?>

                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?= $row->name ?></span>
                                            <div>
                                                <span class="text-muted"><?= l('pay.custom_plan.summary.' . ($row->type == 'inclusive' ? 'tax_inclusive' : 'tax_exclusive')) ?>.</span>
                                                <span class="text-muted"><?= $row->description ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <?php if($row->type == 'inclusive'): ?>
                                            <?= nr($row->amount, 2) ?>
                                        <?php else: ?>
                                            <?= '+' . nr($row->amount, 2) ?>
                                        <?php endif ?>
                                        <span><?= $data->payment->currency ?></span>
                                    </td>
                                </tr>

                            <?php endforeach ?>
                        <?php endif ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="d-flex flex-column">
                                <span class="font-weight-bold"><?= l('invoice.total') ?></span>
                                <?php if($is_payment_paid): ?>
                                    <small><?= sprintf(l('invoice.paid_via'), l('pay.custom_plan.' . $data->payment->processor)) ?></small>
                                <?php elseif($is_proforma_invoice && !empty($data->payment->due_datetime)): ?>
                                    <small><?= l('invoice.due_date') ?>: <?= $format_invoice_long_date($data->payment->due_datetime) ?: \Altum\Date::get($data->payment->due_datetime, 1) ?></small>
                                <?php endif ?>
                            </td>
                            <td class="text-right font-weight-bold"><?= nr($data->payment->total_amount, 2) . ' ' . $data->payment->currency ?></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if($this->user->billing->notes): ?>
                    <div class="mt-6">
                        <table class="table invoice-table">
                            <thead>
                            <tr>
                                <th><?= l('invoice.notes') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <?= nl2br($this->user->billing->notes) ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif ?>

            </div>
        </div>
    </div>
</div>
