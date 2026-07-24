<?php defined('ALTUMCODE') || die() ?>

<?php
$language = $data->language ?? settings()->main->default_language;
$timezone = \Altum\Date::$timezone ?: date_default_timezone_get();
$customer_billing = json_decode($data->subscription->user_billing ?? '');
$customer_name = $data->subscription->user_name ?: $data->subscription->user_email;

if(!empty($customer_billing->name) && $customer_billing->name != $customer_name) {
    $customer_name .= ' (' . $customer_billing->name . ')';
}

$format_long_date = function($date) use ($timezone) {
    try {
        return (new \DateTime($date))->setTimezone(new \DateTimeZone($timezone))->format('l, F jS, Y');
    } catch(\Exception $exception) {
        return null;
    }
};

$period_range = null;

try {
    $period_start = (new \DateTime($data->invoice->period_start))->setTimezone(new \DateTimeZone($timezone));
    $period_end = (new \DateTime($data->invoice->period_end))->setTimezone(new \DateTimeZone($timezone))->modify('-1 day');
    $period_range = '(' . $period_start->format('d/m/Y') . ' - ' . $period_end->format('d/m/Y') . ')';
} catch(\Exception $exception) {
    $period_range = null;
}

$format_money = function($amount) use ($data) {
    return nr($amount, 2) . ' ' . $data->invoice->currency;
};

$payment_processor = $data->subscription->processor ?: 'offline_payment';
$payment_method_label = l('pay.custom_plan.' . $payment_processor, $language, true) ?? ucwords(str_replace('_', ' ', $payment_processor));
$offline_payment_instructions = trim(settings()->offline_payment->instructions ?? '');
?>

<p style="font-size: 14px; line-height: 1.5; margin: 0 0 14px;">
    Hello <strong><?= e($customer_name) ?></strong>,
</p>

<p style="font-size: 14px; line-height: 1.5; margin: 0 0 14px;">
    This is a notice that an invoice has been generated on <?= e($format_long_date($data->invoice->datetime) ?: \Altum\Date::get($data->invoice->datetime, 1)) ?>.
</p>

<p style="font-size: 14px; line-height: 1.5; margin: 0 0 14px;">
    Your payment method is: <strong><?= e($payment_method_label) ?></strong>
</p>

<p style="font-size: 14px; line-height: 1.5; margin: 0 0 14px;">
    <strong><?= e($payment_method_label) ?></strong><br />
    Account Number <strong><?= e($data->invoice->invoice_number) ?></strong><br />
    Amount <strong><?= e($format_money($data->invoice->total_amount)) ?></strong>
</p>

<p style="font-size: 14px; line-height: 1.5; margin: 0 0 18px;">
    You can log in to your client area to view and pay the invoice at
    <a href="<?= $data->invoice_url ?>" style="color: <?= settings()->smtp->button_background_color ?? '#15c' ?>; word-break: break-all;"><?= $data->invoice_url ?></a>
</p>

<?php if(!empty($data->pay_now_url)): ?>
    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 0 0 18px;">
        <tbody>
        <tr>
            <td align="left">
                <a href="<?= $data->pay_now_url ?>" style="background-color: <?= settings()->smtp->button_background_color ?? '#1b1b1b' ?>; border-radius: <?= settings()->smtp->button_border_radius ?? '10' ?>px; color: <?= settings()->smtp->button_text_color ?? '#ffffff' ?>; display: inline-block; font-size: 15px; font-weight: 700; line-height: 1.2; padding: 12px 18px; text-decoration: none;">
                    Pay now
                </a>
            </td>
        </tr>
        <?php if(!empty($data->pay_now_processor_label)): ?>
            <tr>
                <td style="color: #64748b; font-size: 13px; line-height: 1.5; padding-top: 8px;">
                    Recommended payment method: <?= e($data->pay_now_processor_label) ?>
                </td>
            </tr>
        <?php endif ?>
        </tbody>
    </table>
<?php endif ?>

<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 0 0 16px;">
    <tbody>
    <tr>
        <td style="font-size: 14px; line-height: 1.5; padding: 2px 0;">Proforma Invoice #<?= e($data->invoice->invoice_number) ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; line-height: 1.5; padding: 2px 0;">Invoice Date: <?= e($format_long_date($data->invoice->datetime) ?: \Altum\Date::get($data->invoice->datetime, 1)) ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; line-height: 1.5; padding: 2px 0;">Amount Due: <?= e($format_money($data->invoice->total_amount)) ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; line-height: 1.5; padding: 2px 0;">Due Date: <?= e($format_long_date($data->invoice->due_datetime) ?: \Altum\Date::get($data->invoice->due_datetime, 1)) ?></td>
    </tr>
    </tbody>
</table>

<hr style="border: 0; border-top: 1px solid #9ca3af; margin: 18px 0;" />

<h3 style="font-size: 17px; line-height: 1.35; margin: 0 0 12px; font-weight: 700;">Invoice Items</h3>

<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 0 0 12px;">
    <tbody>
    <tr>
        <td style="font-size: 14px; line-height: 1.5; padding: 0 10px 6px 0;">
            <?= e($data->plan->name ?? $data->subscription->plan_id) ?><?= $period_range ? ' ' . e($period_range) : null ?><br />
            <span style="color: #64748b;"><?= e(ucfirst($data->invoice->frequency)) ?> plan x 1</span>
        </td>
        <td align="right" style="font-size: 14px; line-height: 1.5; padding: 0 0 6px 10px; white-space: nowrap;">
            <?= e($format_money($data->invoice->subtotal_amount)) ?>
        </td>
    </tr>
    <tr>
        <td style="border-top: 1px dashed #9ca3af; font-size: 14px; line-height: 1.5; padding: 8px 10px 0 0;">Sub Total:</td>
        <td align="right" style="border-top: 1px dashed #9ca3af; font-size: 14px; line-height: 1.5; padding: 8px 0 0 10px; white-space: nowrap;"><?= e($format_money($data->invoice->subtotal_amount)) ?></td>
    </tr>
    <?php if((float) $data->invoice->tax_amount > 0): ?>
        <tr>
            <td style="font-size: 14px; line-height: 1.5; padding: 2px 10px 0 0;">Tax:</td>
            <td align="right" style="font-size: 14px; line-height: 1.5; padding: 2px 0 0 10px; white-space: nowrap;"><?= e($format_money($data->invoice->tax_amount)) ?></td>
        </tr>
    <?php endif ?>
    <tr>
        <td style="font-size: 14px; line-height: 1.5; padding: 2px 10px 0 0;"><strong>Total:</strong></td>
        <td align="right" style="font-size: 14px; line-height: 1.5; padding: 2px 0 0 10px; white-space: nowrap;"><strong><?= e($format_money($data->invoice->total_amount)) ?></strong></td>
    </tr>
    </tbody>
</table>

<hr style="border: 0; border-top: 1px solid #9ca3af; margin: 18px 0;" />

<h3 style="font-size: 17px; line-height: 1.35; margin: 0 0 12px; font-weight: 700;">How to confirm payment?</h3>

<?php if($offline_payment_instructions): ?>
    <div style="font-size: 14px; line-height: 1.5; margin: 0 0 18px;">
        <?= nl2br($offline_payment_instructions) ?>
    </div>
<?php else: ?>
    <p style="font-size: 14px; line-height: 1.5; margin: 0 0 18px;">
        Please use the invoice number as your payment reference. Your payment will be allocated after confirmation.
    </p>
<?php endif ?>

<p style="font-size: 14px; line-height: 1.5; margin: 0 0 6px;">
    Your partner in service,
</p>

<p style="font-size: 14px; line-height: 1.5; margin: 0;">
    The <?= e(settings()->main->title) ?> Team
</p>
