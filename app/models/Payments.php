<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Models;

defined('ALTUMCODE') || die();

class Payments extends Model {

    public function webhook_process_payment($payment_processor, $external_payment_id, $payment_total, $payment_currency, $user_id, $plan_id, $payment_frequency, $code, $discount_amount, $base_amount, $taxes_ids, $payment_type, $payment_subscription_id, $payer_email, $payer_name, $extra_data = []) {
        /* Get the plan details */
        $plan = db()->where('plan_id', $plan_id)->getOne('plans');

        /* Just make sure the plan is still existing */
        if(!$plan) {
            http_response_code(400);die();
        }

        /* Make sure the transaction is not already existing */
        if(db()->where('payment_id', $external_payment_id)->where('processor', $payment_processor)->has('payments')) {
            http_response_code(400);die();
        }

        /* Make sure the account still exists */
        $user = db()->where('user_id', $user_id)->getOne('users');

        if(!$user) {
            http_response_code(400);die();
        }

        /* Unsubscribe from the previous plan if needed */
        if(!empty($user->payment_subscription_id) && ($payment_subscription_id && $user->payment_subscription_id != $payment_subscription_id)) {
            try {
                (new User())->cancel_subscription($user_id);
            } catch (\Exception $exception) {
                if(DEBUG) {
                    error_log($exception->getMessage());
                }
                echo $exception->getMessage();
                http_response_code(400); die();
            }
        }

        /* Codes */
        $code = (new Payments())->codes_payment_check($code, $user);

        /* Currency exchange in case its needed */
        $total_amount_default_currency = $payment_total;

        if(settings()->payment->default_currency != $payment_currency && settings()->payment->currency_exchange_api_key) {
            try {
                $response = \Unirest\Request::get('https://api.freecurrencyapi.com/v1/latest?apikey=' . settings()->payment->currency_exchange_api_key . '&base_currency=' . $payment_currency . '&currencies=' . settings()->payment->default_currency);

                if($response->code == 200) {
                    $total_amount_default_currency = $payment_total * $response->body->data->{settings()->payment->default_currency};
                    $total_amount_default_currency = number_format($total_amount_default_currency, 2, '.', '');
                }
            } catch (\Exception $exception) {
                /* :) */
            }
        }

        $payment_datetime = get_date();

        /* Add a log into the database */
        $payment_id = db()->insert('payments', [
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'processor' => $payment_processor,
            'type' => $payment_type,
            'frequency' => $payment_frequency,
            'code' => $code ? $code->code : null,
            'discount_amount' => $discount_amount,
            'base_amount' => $base_amount,
            'email' => $payer_email,
            'payment_id' => $external_payment_id,
            'name' => $payer_name,
            'plan' => json_encode(db()->where('plan_id', $plan_id)->getOne('plans', ['plan_id', 'name'])),
            'billing' => settings()->payment->taxes_and_billing_is_enabled && $user->billing ? $user->billing : null,
            'business' => json_encode(settings()->business),
            'taxes_ids' => $taxes_ids,
            'total_amount' => $payment_total,
            'total_amount_default_currency' => $total_amount_default_currency,
            'currency' => $payment_currency,
            'datetime' => $payment_datetime
        ]);

        /* Update the user with the new plan */
        $current_plan_expiration_date = $plan_id == $user->plan_id ? $user->plan_expiration_date : '';
        $subscription_model = new Subscription();
        $renewal_period = $subscription_model->calculate_renewal_period($current_plan_expiration_date, $payment_frequency, $payment_datetime);
        $plan_expiration_date = $renewal_period['period_end'];

        try {
            $subscription_model->sync_from_successful_payment([
                'local_payment_id' => $payment_id,
                'external_payment_id' => $external_payment_id,
                'payment_processor' => $payment_processor,
                'payment_total' => $payment_total,
                'total_amount_default_currency' => $total_amount_default_currency,
                'payment_currency' => $payment_currency,
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'plan_name' => $plan->name,
                'payment_frequency' => $payment_frequency,
                'code' => $code ? $code->code : null,
                'discount_amount' => $discount_amount,
                'base_amount' => $base_amount,
                'taxes_ids' => $taxes_ids,
                'payment_type' => $payment_type,
                'payment_subscription_id' => $payment_subscription_id,
                'payment_datetime' => $payment_datetime,
                'period_start' => $renewal_period['period_start'],
                'period_end' => $plan_expiration_date,
                'billing' => settings()->payment->taxes_and_billing_is_enabled && $user->billing ? $user->billing : null,
                'business' => json_encode(settings()->business),
                'invoice_id' => !empty($extra_data['invoice_id']) ? (int) $extra_data['invoice_id'] : (!empty($_REQUEST['invoice_id']) ? (int) $_REQUEST['invoice_id'] : null),
            ]);
        } catch(\Exception $exception) {
            if(DEBUG) {
                error_log($exception->getMessage());
            }
        }

        /* Database query */
        db()->where('user_id', $user_id)->update('users', [
            'plan_id' => $plan_id,
            'plan_settings' => $plan->settings,
            'plan_expiration_date' => $plan_expiration_date,
            'plan_expiry_reminder' => 0,
            'plan_trial_done' => 1,
            'payment_subscription_id' => $payment_subscription_id,
            'payment_processor' => $payment_processor,
            'payment_total_amount' => $payment_total,
            'payment_currency' => $payment_currency,
        ]);

        /* Run potential hooks */
        \Altum\CustomHooks::user_payment_finished(['user' => $user, 'plan' => $plan]);

        /* Clear the cache */
        cache()->deleteItemsByTag('user_id=' . $user_id);

        /* Send rich confirmation notification to the user */
        self::send_user_payment_confirmation_email($user, $plan, [
            'payment_id' => $payment_id,
            'total_amount' => $payment_total,
            'currency' => $payment_currency,
            'frequency' => $payment_frequency,
            'processor' => $payment_processor,
            'payment_code' => !empty($code) && is_object($code) ? $code->code : $external_payment_id,
            'expiration_date' => $plan_expiration_date,
        ]);

        /* Send notification to admin if needed */
        if(settings()->email_notifications->new_payment && !empty(settings()->email_notifications->emails)) {

            $email_template = get_email_template(
                [
                    '{{PROCESSOR}}' => $payment_processor,
                    '{{TOTAL_AMOUNT}}' => $payment_total,
                    '{{CURRENCY}}' => $payment_currency,
                ],
                l('global.emails.admin_new_payment_notification.subject'),
                [
                    '{{PROCESSOR}}' => $payment_processor,
                    '{{TOTAL_AMOUNT}}' => $payment_total,
                    '{{CURRENCY}}' => $payment_currency,
                    '{{NAME}}' => $user->email,
                    '{{EMAIL}}' => $user->email,
                ],
                l('global.emails.admin_new_payment_notification.body')
            );

            send_mail(explode(',', settings()->email_notifications->emails), $email_template->subject, $email_template->body);

        }

        /* Send webhook notification if needed */
        if(settings()->webhooks->payment_new) {

            fire_and_forget('post', settings()->webhooks->payment_new, [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'name' => $user->name,
                'plan_id' => $plan_id,
                'plan_expiration_date' => $plan_expiration_date,
                'payment_id' => $payment_id,
                'payment_processor' => $payment_processor,
                'payment_type' => $payment_type,
                'payment_frequency' => $payment_frequency,
                'payment_total_amount' => $payment_total,
                'payment_currency' => $payment_currency,
                'payment_code' => $code->code,
                'datetime' => $payment_datetime,
            ]);

        }

        if(settings()->internal_notifications->admins_is_enabled && settings()->internal_notifications->new_payment) {
            db()->insert('internal_notifications', [
                'for_who' => 'admin',
                'from_who' => 'system',
                'icon' => 'fas fa-credit-card',
                'title' => l('global.notifications.new_payment.title'),
                'description' => sprintf(l('global.notifications.new_payment.description'), $user->name, $user->email, $payment_total, $payment_currency, l('pay.custom_plan.' . $payment_processor)),
                'url' => 'admin/payments',
                'datetime' => get_date(),
            ]);
        }

        /* Affiliate */
        (new Payments())->affiliate_payment_check($payment_id, $total_amount_default_currency, settings()->payment->default_currency, $user);
    }

    public function codes_payment_check($code, $user) {
        /* Make sure the code exists */
        $codes_code = db()->where('code', $code)->where('type', 'discount')->getOne('codes');

        if($codes_code) {
            /* Check if we should insert the usage of the code or not */
            if(!db()->where('user_id', $user->user_id)->where('code_id', $codes_code->code_id)->has('redeemed_codes')) {

                /* Update the code usage */
                db()->where('code_id', $codes_code->code_id)->update('codes', ['redeemed' => db()->inc()]);

                /* Add log for the redeemed code */
                db()->insert('redeemed_codes', [
                    'code_id'   => $codes_code->code_id,
                    'user_id'   => $user->user_id,
                    'datetime'  => get_date()
                ]);
            }

            return $codes_code;
        }

        return null;
    }

    public function affiliate_payment_check($payment_id, $payment_total, $payment_currency, $user) {
        if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled && $user->referred_by) {
            if((settings()->affiliate->commission_type == 'once' && !$user->referred_by_has_converted) || settings()->affiliate->commission_type == 'forever') {
                $referral_user = db()->where('user_id', $user->referred_by)->getOne('users', ['user_id', 'email', 'status', 'plan_settings']);
                $referral_user->plan_settings = json_decode($referral_user->plan_settings);

                /* Make sure the referral user is active and existing */
                if($referral_user && $referral_user->status == 1) {
                    $amount = number_format($payment_total * (float) $referral_user->plan_settings->affiliate_commission_percentage / 100, 2, '.', '');

                    /* Insert the affiliate commission */
                    db()->insert('affiliates_commissions', [
                        'user_id' => $referral_user->user_id,
                        'referred_user_id' => $user->user_id,
                        'payment_id' => $payment_id,
                        'amount' => $amount,
                        'currency' => $payment_currency,
                        'datetime' => get_date()
                    ]);

                    /* Update the referred user */
                    db()->where('user_id', $user->user_id)->update('users', ['referred_by_has_converted' => 1]);
                }
            }
        }
    }


    public static function generate_payment_pdf($payment_id, $user = null) {
        $payment = is_object($payment_id) ? $payment_id : db()->where('id', (int) $payment_id)->getOne('payments');
        if(!$payment) {
            return null;
        }

        if(!$user) {
            $user = db()->where('user_id', $payment->user_id)->getOne('users');
        }

        $payment->billing = is_string($payment->billing) ? json_decode($payment->billing) : ($payment->billing ?? null);
        if(empty($payment->billing->name) && !empty($user->billing)) {
            $user_billing = is_string($user->billing) ? json_decode($user->billing) : $user->billing;
            if(!empty($user_billing->name)) {
                $payment->billing = $user_billing;
            }
        }
        $payment->business = is_string($payment->business) ? json_decode($payment->business) : ($payment->business ?? null);
        $payment->plan = is_string($payment->plan) ? json_decode($payment->plan) : ($payment->plan ?? null);

        if(!$payment->business) {
            $payment->business = json_decode(json_encode(settings()->business));
        }

        $prefix = trim((string) ($payment->business->invoice_nr_prefix ?? ''));
        if($prefix === '' && !empty(settings()->business->invoice_nr_prefix)) {
            $prefix = trim((string) settings()->business->invoice_nr_prefix);
        }
        $payment->business->invoice_nr_prefix = in_array($prefix, ['', 'INV-'], true) ? 'INV-642021' : $prefix;

        $linked_invoice = db()->where('payment_id', $payment->id)->orderBy('invoice_id', 'DESC')->getOne('invoices');

        if($linked_invoice) {
            $metadata = json_decode($linked_invoice->metadata ?? '', true);
            if(!is_array($metadata)) {
                $metadata = [];
            }
            $item = db()->where('invoice_id', $linked_invoice->invoice_id)->getOne('invoice_items');
            $item_metadata = $item ? json_decode($item->metadata ?? '', true) : [];
            if(!is_array($item_metadata)) {
                $item_metadata = [];
            }

            if($linked_invoice->status == 'paid' && !empty($linked_invoice->paid_datetime)) {
                $period_start = $linked_invoice->paid_datetime;
                $period_end = null;
            } else {
                $period_start = $metadata['period_start'] ?? $item_metadata['period_start'] ?? $linked_invoice->due_datetime ?? $payment->datetime;
                $period_end = $metadata['period_end'] ?? $item_metadata['period_end'] ?? null;
            }

            if(!$period_end && $period_start && $payment->frequency) {
                $period_end = (new \Altum\Models\Subscription())->calculate_period_end($period_start, $payment->frequency);
            }

            $payment->invoice_number = $linked_invoice->invoice_id;
            $payment->invoice_status = $linked_invoice->status;
            $payment->is_proforma = $linked_invoice->type == 'proforma';
            $payment->due_datetime = $linked_invoice->due_datetime;
            $payment->paid_datetime = $linked_invoice->paid_datetime;
            $payment->period_start = $period_start;
            $payment->period_end = $period_end;
        } else {
            $payment->invoice_number = $payment->id;
            $payment->invoice_status = $payment->status ? 'paid' : 'draft';
            $payment->is_proforma = false;
            $payment->due_datetime = null;
            $payment->paid_datetime = $payment->status ? $payment->datetime : null;
            $payment->period_start = $payment->datetime;
            $payment->period_end = (new \Altum\Models\Subscription())->calculate_period_end($payment->period_start, $payment->frequency);
        }

        $payment->plan_db = (new \Altum\Models\Plan())->get_plan_by_id($payment->plan_id);
        $payment_taxes = (new \Altum\Models\Plan())->get_plan_taxes_by_taxes_ids($payment->taxes_ids) ?: [];

        $base_amount = (float) (($payment->base_amount ?? 0) ?: ($payment->total_amount ?? 0));
        $discount_amount = (float) ($payment->discount_amount ?? 0);
        $payment->price = $discount_amount ? $base_amount - $discount_amount : $base_amount;

        if(!empty($payment_taxes)) {
            $inclusive_taxes_total_percentage = 0;
            foreach($payment_taxes as $row) {
                if($row->type == 'exclusive') continue;
                $inclusive_taxes_total_percentage += $row->value;
            }

            $total_inclusive_tax = $inclusive_taxes_total_percentage > 0 ? $payment->price - ($payment->price / (1 + $inclusive_taxes_total_percentage / 100)) : 0;
            $price_without_inclusive_taxes = $payment->price - $total_inclusive_tax;

            foreach($payment_taxes as $key => $row) {
                if($row->type == 'exclusive') continue;
                $percentage_of_total_inclusive_tax = $inclusive_taxes_total_percentage > 0 ? $row->value * 100 / $inclusive_taxes_total_percentage : 0;
                $payment_taxes[$key]->amount = round($total_inclusive_tax * $percentage_of_total_inclusive_tax / 100, 2);
            }

            foreach($payment_taxes as $key => $row) {
                if($row->type == 'inclusive') continue;
                $payment_taxes[$key]->amount = round($row->value_type == 'percentage' ? $price_without_inclusive_taxes * ($row->value / 100) : $row->value, 2);
            }
        }

        require_once APP_PATH . 'helpers/invoice_pdf.php';
        $user_notes = is_object($payment->billing) ? ($payment->billing->notes ?? null) : null;
        $pdf_content = (new \Altum\Helpers\InvoicePdf())->render($payment, $payment_taxes, ['notes' => $user_notes]);

        $type = !empty($payment->is_proforma) ? 'proforma-invoice' : 'invoice';
        $number = ($payment->business->invoice_nr_prefix ?? '') . ($payment->invoice_number ?? $payment->id);
        $filename = strtolower(trim(preg_replace('/[^a-zA-Z0-9-_]+/', '-', $type . '-' . $number), '-')) . '.pdf';

        return [
            'content' => $pdf_content,
            'filename' => $filename,
            'invoice_number' => $number,
            'payment' => $payment,
        ];
    }

    public static function send_user_payment_confirmation_email($user, $plan, $data = []) {
        /* If user is user_id, retrieve user object */
        if(is_numeric($user)) {
            $user = db()->where('user_id', (int) $user)->getOne('users');
        }
        if(!$user || empty($user->email)) {
            return false;
        }

        /* If plan is plan_id or string, retrieve plan object */
        if(is_string($plan) || is_numeric($plan)) {
            $plan = (new \Altum\Models\Plan())->get_plan_by_id($plan);
        }
        $plan_name = $plan->name ?? ($data['plan_name'] ?? 'Custom');

        /* Determine user greeting */
        $user_name = !empty($user->name) ? $user->name : explode('@', $user->email)[0];
        $website_title = settings()->main->title ?: 'SeamlessQRCode';
        $currency = $data['currency'] ?? ($data['payment_currency'] ?? settings()->payment->default_currency);
        $total_amount = isset($data['total_amount']) ? $data['total_amount'] : ($data['payment_total_amount'] ?? ($data['payment_total'] ?? ($data['amount'] ?? 0)));
        $frequency = $data['frequency'] ?? ($data['payment_frequency'] ?? 'monthly');
        $frequency_label = l('plan.custom_plan.' . $frequency) ?: ucfirst($frequency);

        /* Format amount with currency symbol or code */
        $currency_upper = strtoupper((string) $currency);
        if($currency_upper == 'USD') {
            $formatted_amount = '$' . nr($total_amount, 2);
        } else if($currency_upper == 'EUR') {
            $formatted_amount = '€' . nr($total_amount, 2);
        } else if($currency_upper == 'GBP') {
            $formatted_amount = '£' . nr($total_amount, 2);
        } else if($currency_upper == 'KES') {
            $formatted_amount = 'KES ' . nr($total_amount, 2);
        } else {
            $formatted_amount = nr($total_amount, 2) . ' ' . $currency_upper;
        }

        /* Payment ID and Invoice PDF generation */
        $payment_id = !empty($data['payment_id']) ? (int) $data['payment_id'] : null;
        $pdf_attachment = null;
        $invoice_number_display = null;

        if($payment_id) {
            try {
                $pdf_data = self::generate_payment_pdf($payment_id, $user);
                if($pdf_data && !empty($pdf_data['content'])) {
                    $pdf_attachment = [
                        'content' => $pdf_data['content'],
                        'name' => $pdf_data['filename'] ?? ('invoice-' . $payment_id . '.pdf'),
                        'type' => 'application/pdf',
                        'encoding' => 'base64',
                    ];
                    $invoice_number_display = $pdf_data['invoice_number'] ?? $payment_id;
                }
            } catch(\Throwable $e) {
                if(DEBUG) {
                    error_log('PDF Invoice Generation Error: ' . $e->getMessage());
                }
            }
        }

        /* Transaction / reference code */
        $payment_code = $data['payment_code'] ?? ($data['code'] ?? ($data['external_payment_id'] ?? ($invoice_number_display ?: ($payment_id ?: ''))));
        if(empty($payment_code)) {
            $payment_code = strtoupper(substr(md5(uniqid((string) rand(), true)), 0, 10));
        }
        if(!$invoice_number_display) {
            $invoice_number_display = $payment_code;
        }

        $processor = $data['processor'] ?? ($data['payment_processor'] ?? 'offline_payment');

        /* Payment method display */
        if($processor == 'offline_payment') {
            if(!empty($data['code']) && preg_match('/^[A-Z0-9]{8,12}$/i', (string) $data['code'])) {
                $payment_method = 'M-PESA (Ref: ' . $data['code'] . ')';
            } else if(!empty($data['confirmation_message']) && stripos((string) $data['confirmation_message'], 'MPESA') !== false) {
                $payment_method = 'M-PESA PAYBILL 522522';
            } else {
                $payment_method = 'M-PESA PAYBILL 522522' . (!empty($payment_code) && $payment_code !== 'offline_payment' ? ' (Ref: ' . $payment_code . ')' : '');
            }
        } else if(in_array($processor, ['stripe', 'paypal', 'paystack', 'razorpay', 'iyzico', 'payu', 'crypto', 'coinbase'])) {
            $payment_method = ucfirst($processor) . ' (' . ($payment_code ? 'Ref: ' . $payment_code : 'Online') . ')';
        } else {
            $payment_method = ucfirst(str_replace('_', ' ', (string) $processor));
            if(!empty($payment_code)) {
                $payment_method .= ' (Ref: ' . $payment_code . ')';
            }
        }

        /* Expiration / Renewal date */
        $expiration_date = $data['expiration_date'] ?? ($data['plan_expiration_date'] ?? ($data['period_end'] ?? null));
        if($frequency == 'lifetime') {
            $renewal_date_display = 'Never (Lifetime Access)';
            $estimated_next_charge = 'None (Lifetime Access)';
        } else {
            if(!empty($expiration_date)) {
                try {
                    $dt = new \DateTime($expiration_date);
                    $renewal_date_display = $dt->format('d-M-Y H:i');
                } catch(\Throwable $e) {
                    $renewal_date_display = \Altum\Date::get($expiration_date, 2);
                }
            } else {
                $renewal_date_display = 'Active';
            }
            $estimated_next_charge = $formatted_amount;
        }

        $support_email = !empty(settings()->smtp->from) ? settings()->smtp->from : 'support@seamlessqrcode.com';
        $support_url = url('contact');
        $portal_url = url('account-payments');
        $invoice_view_url = $payment_id ? url('invoice/' . $payment_id . '?email=' . urlencode($user->email)) : $portal_url;
        $invoice_download_url = $payment_id ? url('invoice/download/' . $payment_id . '?email=' . urlencode($user->email)) : $portal_url;

        /* Subject matching the MaxCut structure */
        $subject = sprintf('Congratulations on your %s Subscription for #%s', $website_title, $payment_code);

        /* Rich HTML Body */
        $body = '
        <div style="font-family: -apple-system, BlinkMacSystemFont, Arial, sans-serif; color: #2d3748; line-height: 1.6; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #1a202c; margin-top: 0; margin-bottom: 18px; font-size: 22px; font-weight: 700;">Your subscription is active</h2>
            
            <p style="margin-bottom: 16px; font-size: 16px;">
                Hi ' . htmlspecialchars($user_name) . ',
            </p>
            
            <p style="margin-bottom: 16px; font-size: 16px;">
                Congratulations on your new paid <strong>' . htmlspecialchars($website_title) . '</strong> subscription. Your account is now active and ready. As soon as you log in you can get started creating dynamic QR codes, customizing short links, and utilizing all premium features.
            </p>
            
            <p style="margin-bottom: 24px; font-size: 15px; color: #4a5568;">
                If at any point you need assistance visit our <a href="' . $support_url . '" style="color: #007bff; text-decoration: underline;">support centre</a> or email us at <a href="mailto:' . $support_email . '" style="color: #007bff; text-decoration: underline;">' . $support_email . '</a>.
            </p>

            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                    <tbody>
                        <tr style="border-bottom: 2px solid #e2e8f0;">
                            <td colspan="2" style="padding: 0 0 10px 0; color: #718096; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">
                                Subscription & Invoice summary
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #edf2f7;">
                            <td style="padding: 12px 0; font-weight: 600; color: #2d3748;">
                                ' . htmlspecialchars($website_title . ' ' . $plan_name . ' ' . $frequency_label . ' Subscription') . '
                            </td>
                            <td style="padding: 12px 0; text-align: right; font-weight: 600; color: #2d3748;">
                                ' . $formatted_amount . ' &times; 1 units
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #edf2f7;">
                            <td style="padding: 12px 0; color: #718096;">Invoice number</td>
                            <td style="padding: 12px 0; text-align: right; font-weight: 700; color: #1a202c;">#' . htmlspecialchars($invoice_number_display) . '</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #edf2f7;">
                            <td style="padding: 12px 0; color: #718096;">Invoiced amount</td>
                            <td style="padding: 12px 0; text-align: right; font-weight: 700; color: #1a202c;">' . $formatted_amount . ' (Paid)</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #edf2f7;">
                            <td style="padding: 12px 0; color: #718096;">Payment method</td>
                            <td style="padding: 12px 0; text-align: right; color: #2d3748;">' . htmlspecialchars($payment_method) . '</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #edf2f7;">
                            <td style="padding: 12px 0; color: #718096;">Next renewal on</td>
                            <td style="padding: 12px 0; text-align: right; color: #2d3748;">' . htmlspecialchars($renewal_date_display) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 0 0 0; color: #718096;">Estimated charge on next renewal</td>
                            <td style="padding: 12px 0 0 0; text-align: right; color: #2d3748;">' . htmlspecialchars($estimated_next_charge) . '</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p style="margin-bottom: 14px; font-size: 15px; color: #4a5568;">
                Your official tax invoice is attached to this email as a PDF document. You can also view or download it anytime online:
            </p>

            <div style="margin: 16px 0 26px 0;">
                <a href="' . $invoice_download_url . '" class="cta" style="background-color: #10b981; color: #ffffff; padding: 11px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; display: inline-block; margin-right: 8px; margin-bottom: 8px;">
                    📄 Download PDF Invoice
                </a>
                <a href="' . $invoice_view_url . '" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 11px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; display: inline-block; margin-bottom: 8px;">
                    View Invoice Online &rarr;
                </a>
            </div>

            <p style="margin-bottom: 14px; font-size: 15px; color: #4a5568;">
                Manage your payments, update settings, or view your QR codes on your customer portal:
            </p>

            <div style="margin: 16px 0 28px 0;">
                <a href="' . $portal_url . '" class="cta" style="background-color: #007bff; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; display: inline-block;">
                    Log into your account now &rarr;
                </a>
            </div>

            <p style="margin-bottom: 24px; font-size: 15px; color: #2d3748;">
                Regards,<br />
                <strong>' . htmlspecialchars($website_title) . ' Support</strong>
            </p>

            <p style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #718096; font-style: italic;">
                PS: We hope you\'re enjoying your experience with us! As always, feel free to reach out to us. We\'d love to hear from you.
            </p>
        </div>';

        $mail_data = [
            'anti_phishing_code' => $user->anti_phishing_code ?? null,
            'language' => $user->language ?? settings()->main->default_language
        ];

        if(!empty($pdf_attachment)) {
            $mail_data['string_attachments'] = [$pdf_attachment];
        }

        return send_mail(
            $user->email,
            $subject,
            $body,
            $mail_data
        );
    }
}
