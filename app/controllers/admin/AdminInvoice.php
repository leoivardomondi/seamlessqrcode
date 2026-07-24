<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum\Controllers;

use Altum\Models\Plan;
use Altum\Models\Subscription;
use Altum\Title;

defined('ALTUMCODE') || die();

class AdminInvoice extends Controller {

    public function index() {

        $id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Make sure the campaign exists and is accessible to the user */
        if(!$payment = db()->where('id', $id)->getOne('payments')) {
            redirect('admin/payments');
        }

        /* Try to see if we get details from the billing */
        $payment->billing = json_decode($payment->billing ?? '');
        $payment->business = json_decode($payment->business ?? '');
        $payment->plan = json_decode($payment->plan ?? '');

        if(!$payment->business) {
            $payment->business = json_decode(json_encode(settings()->business));
        }

        $this->normalize_invoice_prefix($payment->business);

        $linked_invoice = db()->where('payment_id', $payment->id)->orderBy('invoice_id', 'DESC')->getOne('invoices');

        if($linked_invoice) {
            $this->apply_invoice_data_to_payment($payment, $linked_invoice);
        } else {
            $payment->invoice_number = $payment->id;
            $payment->invoice_status = $payment->status ? 'paid' : 'draft';
            $payment->is_proforma = false;
            $payment->due_datetime = null;
            $payment->paid_datetime = $payment->status ? $payment->datetime : null;
            $payment->period_start = $payment->datetime;
            $payment->period_end = (new Subscription())->calculate_period_end($payment->period_start, $payment->frequency);
        }

        /* Get the plan details */
        $payment->plan_db = (new Plan())->get_plan_by_id($payment->plan_id);

        /* Check for potential taxes */
        $payment_taxes = (new \Altum\Models\Plan())->get_plan_taxes_by_taxes_ids($payment->taxes_ids);

        /* Calculate the price if a discount was used */
        $payment->price = $payment->discount_amount ? $payment->base_amount - $payment->discount_amount : $payment->base_amount;

        /* Calculate taxes */
        if(!empty($payment_taxes)) {

            /* Check for the inclusives */
            $inclusive_taxes_total_percentage = 0;

            foreach($payment_taxes as $key => $row) {
                if($row->type == 'exclusive') continue;

                $inclusive_taxes_total_percentage += $row->value;
            }

            $total_inclusive_tax = $payment->price - ($payment->price / (1 + $inclusive_taxes_total_percentage / 100));

            $price_without_inclusive_taxes = $payment->price - $total_inclusive_tax;

            foreach($payment_taxes as $key => $row) {
                if($row->type == 'exclusive') continue;

                $percentage_of_total_inclusive_tax = $row->value ? $row->value * 100 / $inclusive_taxes_total_percentage : 0;

                $inclusive_tax = number_format($total_inclusive_tax * $percentage_of_total_inclusive_tax / 100, 2);

                $payment_taxes[$key]->amount = $inclusive_tax;
            }

            /* Check for the exclusives */
            foreach($payment_taxes as $key => $row) {

                if($row->type == 'inclusive') {
                    continue;
                }

                $exclusive_tax = number_format($row->value_type == 'percentage' ? $price_without_inclusive_taxes * ($row->value / 100) : $row->value, 2);

                $payment_taxes[$key]->amount = $exclusive_tax;

            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('invoice.title'), $payment->business->invoice_nr_prefix . $payment->invoice_number));

        /* Prepare the view */
        $data = [
            'payment' => $payment,
            'payment_taxes' => $payment_taxes
        ];

        $view = new \Altum\View('admin/invoice/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function proforma() {

        $invoice_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$invoice = db()->where('invoice_id', $invoice_id)->getOne('invoices')) {
            redirect('admin/payments');
        }

        [$payment, $payment_taxes] = $this->prepare_invoice_row_document($invoice);

        Title::set(sprintf(l('invoice.title'), $payment->business->invoice_nr_prefix . $payment->invoice_number));

        $data = [
            'payment' => $payment,
            'payment_taxes' => $payment_taxes
        ];

        $view = new \Altum\View('admin/invoice/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

    public function download() {

        $id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$payment = db()->where('id', $id)->getOne('payments')) {
            redirect('admin/payments');
        }

        [$payment, $payment_taxes] = $this->prepare_payment_pdf_document($payment);

        $this->output_invoice_pdf($payment, $payment_taxes);
    }

    public function download_proforma() {

        $invoice_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$invoice = db()->where('invoice_id', $invoice_id)->getOne('invoices')) {
            redirect('admin/payments');
        }

        [$payment, $payment_taxes] = $this->prepare_invoice_row_document($invoice);
        $payment_taxes = $this->prepare_payment_taxes($payment, $payment_taxes);

        $this->output_invoice_pdf($payment, $payment_taxes);
    }

    private function apply_invoice_data_to_payment($payment, $invoice) {
        $metadata = json_decode($invoice->metadata ?? '', true);

        if(!is_array($metadata)) {
            $metadata = [];
        }

        $item = db()->where('invoice_id', $invoice->invoice_id)->getOne('invoice_items');
        $item_metadata = $item ? json_decode($item->metadata ?? '', true) : [];

        if(!is_array($item_metadata)) {
            $item_metadata = [];
        }

        if($invoice->status == 'paid' && !empty($invoice->paid_datetime)) {
            $period_start = $invoice->paid_datetime;
            $period_end = null;
        } else {
            $period_start = $metadata['period_start'] ?? $item_metadata['period_start'] ?? $invoice->due_datetime ?? $payment->datetime;
            $period_end = $metadata['period_end'] ?? $item_metadata['period_end'] ?? null;
        }

        if(!$period_end && $period_start && $payment->frequency) {
            $period_end = (new Subscription())->calculate_period_end($period_start, $payment->frequency);
        }

        $payment->invoice_number = $invoice->invoice_id;
        $payment->invoice_status = $invoice->status;
        $payment->is_proforma = $invoice->type == 'proforma';
        $payment->due_datetime = $invoice->due_datetime;
        $payment->paid_datetime = $invoice->paid_datetime;
        $payment->period_start = $period_start;
        $payment->period_end = $period_end;
    }

    private function prepare_invoice_row_document($invoice) {
        $plan_model = new Plan();
        $plan = $plan_model->get_plan_by_id($invoice->plan_id);
        $item = db()->where('invoice_id', $invoice->invoice_id)->getOne('invoice_items');
        $user = db()->where('user_id', $invoice->user_id)->getOne('users', ['name', 'email']);
        $metadata = json_decode($invoice->metadata ?? '', true);
        $item_metadata = $item ? json_decode($item->metadata ?? '', true) : [];

        if(!is_array($metadata)) {
            $metadata = [];
        }

        if(!is_array($item_metadata)) {
            $item_metadata = [];
        }

        $business = json_decode($invoice->business ?? '');

        if(!$business) {
            $business = json_decode(json_encode(settings()->business));
        }

        $this->normalize_invoice_prefix($business);

        if($invoice->status == 'paid' && !empty($invoice->paid_datetime)) {
            $period_start = $invoice->paid_datetime;
            $period_end = null;
        } else {
            $period_start = $metadata['period_start'] ?? $item_metadata['period_start'] ?? $invoice->due_datetime ?? $invoice->datetime;
            $period_end = $metadata['period_end'] ?? $item_metadata['period_end'] ?? null;
        }

        if(!$period_end && $period_start && $invoice->frequency) {
            $period_end = (new Subscription())->calculate_period_end($period_start, $invoice->frequency);
        }

        $payment = (object) [
            'id' => $invoice->invoice_id,
            'invoice_number' => $invoice->invoice_id,
            'invoice_status' => $invoice->status,
            'is_proforma' => $invoice->type == 'proforma',
            'plan_id' => $invoice->plan_id,
            'billing' => json_decode($invoice->billing ?? ''),
            'business' => $business,
            'plan' => (object) ['name' => $item->description ?? $plan->name ?? $invoice->plan_id],
            'plan_db' => $plan,
            'frequency' => $invoice->frequency,
            'code' => $invoice->code,
            'base_amount' => $invoice->subtotal_amount ?: $invoice->total_amount,
            'discount_amount' => $invoice->discount_amount,
            'total_amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'processor' => $invoice->processor,
            'taxes_ids' => $invoice->taxes_ids,
            'status' => $invoice->status == 'paid' ? 1 : 0,
            'datetime' => $invoice->datetime,
            'due_datetime' => $invoice->due_datetime,
            'paid_datetime' => $invoice->paid_datetime,
            'period_start' => $period_start,
            'period_end' => $period_end,
            'name' => $user->name ?? null,
            'email' => $user->email ?? null,
        ];

        return [$payment, $plan_model->get_plan_taxes_by_taxes_ids($invoice->taxes_ids)];
    }

    private function prepare_payment_pdf_document($payment) {
        $payment->billing = json_decode($payment->billing ?? '');
        $payment->business = json_decode($payment->business ?? '');
        $payment->plan = json_decode($payment->plan ?? '');

        if(!$payment->business) {
            $payment->business = json_decode(json_encode(settings()->business));
        }

        $this->normalize_invoice_prefix($payment->business);

        $linked_invoice = db()->where('payment_id', $payment->id)->orderBy('invoice_id', 'DESC')->getOne('invoices');

        if($linked_invoice) {
            $this->apply_invoice_data_to_payment($payment, $linked_invoice);
        } else {
            $payment->invoice_number = $payment->id;
            $payment->invoice_status = $payment->status ? 'paid' : 'draft';
            $payment->is_proforma = false;
            $payment->due_datetime = null;
            $payment->paid_datetime = $payment->status ? $payment->datetime : null;
            $payment->period_start = $payment->datetime;
            $payment->period_end = (new Subscription())->calculate_period_end($payment->period_start, $payment->frequency);
        }

        $payment->plan_db = (new Plan())->get_plan_by_id($payment->plan_id);
        $payment_taxes = (new Plan())->get_plan_taxes_by_taxes_ids($payment->taxes_ids);
        $payment_taxes = $this->prepare_payment_taxes($payment, $payment_taxes);

        return [$payment, $payment_taxes];
    }

    private function prepare_payment_taxes($payment, $payment_taxes) {
        $payment_taxes = $payment_taxes ?: [];
        $base_amount = (float) (($payment->base_amount ?? 0) ?: ($payment->total_amount ?? 0));
        $discount_amount = (float) ($payment->discount_amount ?? 0);
        $payment->price = $discount_amount ? $base_amount - $discount_amount : $base_amount;

        if(empty($payment_taxes)) {
            return $payment_taxes;
        }

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
            $inclusive_tax = round($total_inclusive_tax * $percentage_of_total_inclusive_tax / 100, 2);

            $payment_taxes[$key]->amount = $inclusive_tax;
        }

        foreach($payment_taxes as $key => $row) {
            if($row->type == 'inclusive') {
                continue;
            }

            $exclusive_tax = round($row->value_type == 'percentage' ? $price_without_inclusive_taxes * ($row->value / 100) : $row->value, 2);

            $payment_taxes[$key]->amount = $exclusive_tax;
        }

        return $payment_taxes;
    }

    private function output_invoice_pdf($payment, array $payment_taxes = []) {
        require_once APP_PATH . 'helpers/invoice_pdf.php';

        $pdf = (new \Altum\Helpers\InvoicePdf())->render($payment, $payment_taxes);
        $filename = $this->get_invoice_pdf_filename($payment);

        while(ob_get_level()) {
            @ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));

        echo $pdf;
        die();
    }

    private function get_invoice_pdf_filename($payment): string {
        $type = !empty($payment->is_proforma) ? 'proforma-invoice' : 'invoice';
        $number = ($payment->business->invoice_nr_prefix ?? '') . ($payment->invoice_number ?? $payment->id);
        $filename = strtolower(trim(preg_replace('/[^a-zA-Z0-9-_]+/', '-', $type . '-' . $number), '-'));

        return ($filename ?: $type) . '.pdf';
    }

    private function normalize_invoice_prefix($business): void {
        $prefix = trim((string) ($business->invoice_nr_prefix ?? ''));

        if($prefix === '' && !empty(settings()->business->invoice_nr_prefix)) {
            $prefix = trim((string) settings()->business->invoice_nr_prefix);
        }

        $business->invoice_nr_prefix = in_array($prefix, ['', 'INV-'], true) ? 'INV-642021' : $prefix;
    }

}
