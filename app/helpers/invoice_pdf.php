<?php

namespace Altum\Helpers;

defined('ALTUMCODE') || die();

class InvoicePdf {

    private array $objects = [];
    private string $content = '';
    private int $font_regular_id;
    private int $font_bold_id;
    private ?array $logo = null;

    private float $page_width = 595.28;
    private float $page_height = 841.89;

    private array $ink = [17, 24, 39];
    private array $muted = [100, 116, 139];
    private array $light = [248, 250, 252];
    private array $border = [226, 232, 240];
    private array $accent = [37, 99, 235];
    private array $success = [22, 163, 74];

    public function render($payment, array $payment_taxes = [], array $options = []): string {
        $this->objects = [];
        $this->content = '';

        $this->font_regular_id = $this->add_object('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>');
        $this->font_bold_id = $this->add_object('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>');
        $this->logo = $this->load_logo();

        $payment_taxes = $this->ensure_tax_amounts($payment, $payment_taxes);

        $is_proforma = !empty($payment->is_proforma);
        $is_paid = (int) ($payment->status ?? 0) === 1 && !$is_proforma;
        $invoice_label = $is_proforma ? $this->label('invoice.proforma_invoice', 'Proforma Invoice') : $this->label('invoice.invoice', 'Invoice');
        $invoice_number = $this->get_invoice_number($payment);
        $plan_name = $payment->plan->name ?? $payment->plan_db->name ?? 'Plan';
        $currency = $payment->currency ?? '';
        $base_amount = (float) (($payment->base_amount ?? 0) ?: ($payment->total_amount ?? 0));
        $discount_amount = (float) ($payment->discount_amount ?? 0);
        $total_amount = (float) ($payment->total_amount ?? $base_amount);

        $this->draw_rect(0, 0, $this->page_width, 7, $this->accent);
        $this->draw_header($payment, $invoice_label, $invoice_number, $is_paid);

        $this->draw_meta_box(48, 113, 499, 68, [
            [$this->label('invoice.invoice_nr', 'Invoice Nr'), $invoice_number],
            [$this->label('invoice.invoice_date', 'Invoice Date'), $this->format_long_date($payment->datetime ?? null)],
            [$is_proforma ? $this->label('invoice.due_date', 'Due Date') : 'Status', $is_proforma ? $this->format_long_date($payment->due_datetime ?? null) : ($is_paid ? 'Paid' : ucwords((string) ($payment->invoice_status ?? 'Draft')))],
        ]);

        $from_lines = $this->business_lines($payment->business ?? null);
        $to_lines = $this->customer_lines($payment);

        $this->draw_party_block(48, 212, 235, $this->label('invoice.vendor', 'Vendor'), $from_lines);
        $this->draw_party_block(312, 212, 235, $this->label('invoice.customer', 'Customer'), $to_lines);

        $table_top = 365;
        $this->draw_table_header($table_top);

        $row_top = $table_top + 42;
        $period_range = $this->format_period_range($payment->period_start ?? null, $payment->period_end ?? null);
        $frequency_label = $this->frequency_label($payment->frequency ?? null);
        $description_lines = array_filter([
            $plan_name,
            $frequency_label ? $frequency_label . ' access' : null,
            $period_range,
        ]);

        $row_top = $this->draw_item_row($row_top, $description_lines, $this->money($base_amount, $currency));

        if($discount_amount > 0) {
            $discount_lines = [$this->label('invoice.code', 'Discount')];
            if(!empty($payment->code)) {
                $discount_lines[] = sprintf($this->label('invoice.code_help', 'Code used: %s'), $payment->code);
            }
            $row_top = $this->draw_item_row($row_top, $discount_lines, '-' . $this->money($discount_amount, $currency));
        }

        foreach($payment_taxes as $tax) {
            $amount = (float) str_replace(',', '', (string) ($tax->amount ?? 0));
            $prefix = ($tax->type ?? null) === 'exclusive' ? '+' : '';
            $tax_lines = [$tax->name ?? 'Tax'];

            if(!empty($tax->description)) {
                $tax_lines[] = $tax->description;
            }

            $row_top = $this->draw_item_row($row_top, $tax_lines, $prefix . $this->money($amount, $currency));
        }

        $totals_top = max($row_top + 18, 560);
        $this->draw_totals($payment, $totals_top, $total_amount, $currency, $is_paid, $is_proforma);

        $notes = $this->extract_notes($payment, $options);
        if($notes) {
            $this->draw_notes(48, $totals_top + 112, 499, $notes);
        }

        $this->draw_footer($payment);

        return $this->build_pdf();
    }

    private function draw_header($payment, string $invoice_label, string $invoice_number, bool $is_paid): void {
        if($this->logo) {
            $ratio = $this->logo['width'] > 0 ? $this->logo['height'] / $this->logo['width'] : 0.3;
            $width = min(158, max(70, $this->logo['width']));
            $height = $width * $ratio;

            if($height > 48) {
                $height = 48;
                $width = $height / max($ratio, 0.01);
            }

            $this->draw_image('Im1', 48, 36, $width, $height);
        } else {
            $brand = $payment->business->brand_name ?? $payment->business->name ?? settings()->main->title ?? 'Invoice';
            $this->draw_text(48, 38, $brand, 18, 'F2', $this->ink);
        }

        $this->draw_text_right(547, 38, $invoice_label, 22, 'F2', $this->ink);
        $this->draw_text_right(547, 67, '#' . $invoice_number, 11, 'F1', $this->muted);

        if($is_paid) {
            $this->draw_rect(477, 88, 70, 25, null, $this->success, 1.4);
            $this->draw_text_center(477, 94, 70, 'PAID', 10, 'F2', $this->success);
        }
    }

    private function get_invoice_number($payment): string {
        $prefix = trim((string) ($payment->business->invoice_nr_prefix ?? ''));

        if($prefix === '' && !empty(settings()->business->invoice_nr_prefix)) {
            $prefix = trim((string) settings()->business->invoice_nr_prefix);
        }

        $prefix = in_array($prefix, ['', 'INV-'], true) ? 'INV-642021' : $prefix;

        return $prefix . ($payment->invoice_number ?? $payment->id);
    }

    private function draw_meta_box(float $x, float $top, float $width, float $height, array $rows): void {
        $this->draw_rect($x, $top, $width, $height, $this->light, $this->border);

        $column_width = $width / max(count($rows), 1);
        foreach($rows as $index => $row) {
            $column_x = $x + ($column_width * $index) + 16;
            $this->draw_text($column_x, $top + 18, (string) $row[0], 8.5, 'F2', $this->muted);
            $this->draw_text($column_x, $top + 39, (string) ($row[1] ?: '-'), 10.5, 'F2', $this->ink);

            if($index > 0) {
                $this->draw_line($x + ($column_width * $index), $top + 14, $x + ($column_width * $index), $top + $height - 14, $this->border);
            }
        }
    }

    private function draw_party_block(float $x, float $top, float $width, string $title, array $lines): void {
        $this->draw_text($x, $top, $title, 10, 'F2', $this->muted);
        $this->draw_rect($x, $top + 22, $width, 102, [255, 255, 255], $this->border);

        $y = $top + 43;
        foreach($lines as $index => $line) {
            $font = $index === 0 ? 'F2' : 'F1';
            $color = $index === 0 ? $this->ink : $this->muted;

            foreach($this->wrap_text($line, $width - 28, 9.5, $font === 'F2') as $wrapped_line) {
                if($y > $top + 109) {
                    return;
                }

                $this->draw_text($x + 14, $y, $wrapped_line, 9.5, $font, $color);
                $y += 14;
            }
        }
    }

    private function draw_table_header(float $top): void {
        $this->draw_text(48, $top - 30, 'Subscription items', 13, 'F2', $this->ink);
        $this->draw_rect(48, $top, 499, 36, $this->accent);
        $this->draw_text(64, $top + 13, $this->label('invoice.item', 'Item'), 9.5, 'F2', [255, 255, 255]);
        $this->draw_text_right(531, $top + 13, $this->label('invoice.amount', 'Amount'), 9.5, 'F2', [255, 255, 255]);
    }

    private function draw_item_row(float $top, array $description_lines, string $amount): float {
        $wrapped_lines = [];

        foreach($description_lines as $index => $line) {
            foreach($this->wrap_text($line, 330, $index === 0 ? 10 : 8.7, $index === 0) as $wrapped_line) {
                $wrapped_lines[] = [
                    'text' => $wrapped_line,
                    'size' => $index === 0 ? 10 : 8.7,
                    'font' => $index === 0 ? 'F2' : 'F1',
                    'color' => $index === 0 ? $this->ink : $this->muted,
                ];
            }
        }

        $height = max(48, 17 + (count($wrapped_lines) * 13));
        $this->draw_rect(48, $top, 499, $height, [255, 255, 255], $this->border);

        $y = $top + 16;
        foreach($wrapped_lines as $line) {
            $this->draw_text(64, $y, $line['text'], $line['size'], $line['font'], $line['color']);
            $y += 13;
        }

        $this->draw_text_right(531, $top + 18, $amount, 10.2, 'F2', $this->ink);

        return $top + $height;
    }

    private function draw_totals($payment, float $top, float $total_amount, string $currency, bool $is_paid, bool $is_proforma): void {
        $this->draw_rect(327, $top, 220, 82, $this->light, $this->border);
        $this->draw_text(343, $top + 18, $this->label('invoice.total', 'Total'), 10, 'F2', $this->muted);
        $this->draw_text_right(531, $top + 17, $this->money($total_amount, $currency), 15, 'F2', $this->ink);
        $this->draw_line(343, $top + 43, 531, $top + 43, $this->border);

        if($is_paid) {
            $processor = $payment->processor ? $this->processor_label($payment->processor) : '-';
            $this->draw_text(343, $top + 60, sprintf($this->label('invoice.paid_via', 'Paid via %s'), $processor), 8.8, 'F1', $this->muted);

            if(!empty($payment->paid_datetime)) {
                $this->draw_text_right(531, $top + 60, $this->format_long_date($payment->paid_datetime), 8.8, 'F1', $this->muted);
            }
        } elseif($is_proforma && !empty($payment->due_datetime)) {
            $this->draw_text(343, $top + 60, $this->label('invoice.due_date', 'Due Date'), 8.8, 'F1', $this->muted);
            $this->draw_text_right(531, $top + 60, $this->format_long_date($payment->due_datetime), 8.8, 'F1', $this->muted);
        }
    }

    private function draw_notes(float $x, float $top, float $width, string $notes): void {
        if($top > 735) {
            return;
        }

        $wrapped = $this->wrap_text($notes, $width - 28, 8.8);
        $wrapped = array_slice($wrapped, 0, 7);
        $height = 38 + (count($wrapped) * 12);

        $this->draw_text($x, $top, $this->label('invoice.notes', 'Additional notes'), 10, 'F2', $this->muted);
        $this->draw_rect($x, $top + 20, $width, $height, [255, 255, 255], $this->border);

        $y = $top + 40;
        foreach($wrapped as $line) {
            $this->draw_text($x + 14, $y, $line, 8.8, 'F1', $this->ink);
            $y += 12;
        }
    }

    private function draw_footer($payment): void {
        $this->draw_line(48, 790, 547, 790, $this->border);
        $brand = $payment->business->brand_name ?? $payment->business->name ?? settings()->main->title ?? '';
        $this->draw_text(48, 809, $brand, 8.2, 'F1', $this->muted);
        $this->draw_text_right(547, 809, SITE_URL ?? '', 8.2, 'F1', $this->muted);
    }

    private function ensure_tax_amounts($payment, array $payment_taxes): array {
        if(!$payment_taxes) {
            return [];
        }

        $base_amount = (float) (($payment->base_amount ?? 0) ?: ($payment->total_amount ?? 0));
        $discount_amount = (float) ($payment->discount_amount ?? 0);
        $price = max(0, $base_amount - $discount_amount);

        $inclusive_percentage = 0;
        foreach($payment_taxes as $tax) {
            if(($tax->type ?? null) !== 'exclusive') {
                $inclusive_percentage += (float) ($tax->value ?? 0);
            }
        }

        $total_inclusive_tax = $inclusive_percentage > 0 ? $price - ($price / (1 + $inclusive_percentage / 100)) : 0;
        $price_without_inclusive_taxes = $price - $total_inclusive_tax;

        foreach($payment_taxes as $tax) {
            if(isset($tax->amount)) {
                continue;
            }

            if(($tax->type ?? null) === 'inclusive') {
                $percentage = $inclusive_percentage ? ((float) ($tax->value ?? 0) * 100 / $inclusive_percentage) : 0;
                $tax->amount = round($total_inclusive_tax * $percentage / 100, 2);
            } else {
                $tax->amount = round(($tax->value_type ?? null) === 'percentage' ? $price_without_inclusive_taxes * ((float) ($tax->value ?? 0) / 100) : (float) ($tax->value ?? 0), 2);
            }
        }

        return $payment_taxes;
    }

    private function business_lines($business): array {
        if(!$business) {
            return [];
        }

        $lines = [];
        $lines[] = $business->name ?? $business->brand_name ?? settings()->main->title ?? '';
        $lines[] = $this->join_address($business);

        if(!empty($business->email)) {
            $lines[] = $business->email;
        }

        if(!empty($business->phone)) {
            $lines[] = $business->phone;
        }

        if(!empty($business->tax_type) && !empty($business->tax_id)) {
            $lines[] = $business->tax_type . ': ' . $business->tax_id;
        }

        foreach(['custom_key_one' => 'custom_value_one', 'custom_key_two' => 'custom_value_two'] as $key => $value) {
            if(!empty($business->{$key}) && !empty($business->{$value})) {
                $lines[] = $business->{$key} . ': ' . $business->{$value};
            }
        }

        return array_values(array_filter($lines));
    }

    private function customer_lines($payment): array {
        $billing = $payment->billing ?? null;
        $lines = [];

        if($billing) {
            $lines[] = $billing->name ?? $payment->name ?? '';
            $lines[] = $this->join_address($billing);

            if(!empty($billing->email)) {
                $lines[] = $billing->email;
            }

            if(!empty($billing->phone)) {
                $lines[] = $billing->phone;
            }

            if(!empty($billing->tax_id)) {
                $lines[] = $this->label('invoice.tax_id', 'Tax ID') . ': ' . $billing->tax_id;
            }
        } else {
            $lines[] = $payment->name ?? '';
            $lines[] = $payment->email ?? '';
        }

        return array_values(array_filter($lines));
    }

    private function join_address($data): ?string {
        if(!$data) {
            return null;
        }

        $parts = [];
        foreach(['address', 'city', 'county', 'zip'] as $key) {
            if(!empty($data->{$key})) {
                $parts[] = $data->{$key};
            }
        }

        if(!empty($data->country)) {
            $parts[] = $this->country_name($data->country);
        }

        return $parts ? implode(', ', $parts) : null;
    }

    private function load_logo(): ?array {
        $keys = ['logo_email', 'logo_light', 'logo_dark'];

        foreach($keys as $key) {
            $file = settings()->main->{$key} ?? null;

            if(!$file) {
                continue;
            }

            $path = \Altum\Uploads::get_full_path($key) . $file;

            if(!is_file($path)) {
                continue;
            }

            $image = $this->prepare_image($path);

            if($image) {
                return $image;
            }
        }

        return null;
    }

    private function prepare_image(string $path): ?array {
        $info = @getimagesize($path);

        if(!$info) {
            return null;
        }

        [$width, $height, $type] = $info;
        $data = null;

        if($type === IMAGETYPE_JPEG) {
            $data = @file_get_contents($path);
        } elseif($type === IMAGETYPE_PNG) {
            return $this->prepare_png_image($path);
        } else {
            $data = $this->convert_image_to_jpeg($path, $type);
        }

        if(!$data) {
            return null;
        }

        $object_id = $this->add_object(
            "<< /Type /XObject /Subtype /Image /Width {$width} /Height {$height} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($data) . " >>\n" .
            "stream\n" . $data . "\nendstream"
        );

        return [
            'id' => $object_id,
            'width' => (float) $width,
            'height' => (float) $height,
        ];
    }

    private function prepare_png_image(string $path): ?array {
        if(!function_exists('zlib_decode') || !function_exists('gzcompress')) {
            return null;
        }

        $png = $this->read_png($path);

        if(!$png || $png['bit_depth'] !== 8 || !in_array($png['color_type'], [2, 6])) {
            return null;
        }

        $channels = $png['color_type'] === 6 ? 4 : 3;
        $decoded = @zlib_decode($png['idat']);

        if($decoded === false) {
            return null;
        }

        $scanlines = $this->decode_png_scanlines($decoded, $png['width'], $png['height'], $channels);

        if(!$scanlines) {
            return null;
        }

        $rgb = '';
        $alpha = '';

        for($i = 0; $i < strlen($scanlines); $i += $channels) {
            $rgb .= $scanlines[$i] . $scanlines[$i + 1] . $scanlines[$i + 2];

            if($channels === 4) {
                $alpha .= $scanlines[$i + 3];
            }
        }

        $smask_id = null;

        if($channels === 4) {
            $alpha_data = gzcompress($alpha);

            if(!$alpha_data) {
                return null;
            }

            $smask_id = $this->add_object(
                "<< /Type /XObject /Subtype /Image /Width {$png['width']} /Height {$png['height']} /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /FlateDecode /Length " . strlen($alpha_data) . " >>\n" .
                "stream\n" . $alpha_data . "\nendstream"
            );
        }

        $image_data = gzcompress($rgb);

        if(!$image_data) {
            return null;
        }

        $object = "<< /Type /XObject /Subtype /Image /Width {$png['width']} /Height {$png['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode";

        if($smask_id) {
            $object .= " /SMask {$smask_id} 0 R";
        }

        $object .= " /Length " . strlen($image_data) . " >>\nstream\n" . $image_data . "\nendstream";

        $object_id = $this->add_object($object);

        return [
            'id' => $object_id,
            'width' => (float) $png['width'],
            'height' => (float) $png['height'],
        ];
    }

    private function read_png(string $path): ?array {
        $data = @file_get_contents($path);

        if(!$data || substr($data, 0, 8) !== "\x89PNG\r\n\x1A\n") {
            return null;
        }

        $offset = 8;
        $width = null;
        $height = null;
        $bit_depth = null;
        $color_type = null;
        $idat = '';

        while($offset + 8 <= strlen($data)) {
            $length = unpack('N', substr($data, $offset, 4))[1];
            $type = substr($data, $offset + 4, 4);
            $chunk = substr($data, $offset + 8, $length);
            $offset += 12 + $length;

            if($type === 'IHDR') {
                $width = unpack('N', substr($chunk, 0, 4))[1];
                $height = unpack('N', substr($chunk, 4, 4))[1];
                $bit_depth = ord($chunk[8]);
                $color_type = ord($chunk[9]);
            } elseif($type === 'IDAT') {
                $idat .= $chunk;
            } elseif($type === 'IEND') {
                break;
            }
        }

        if(!$width || !$height || !$idat) {
            return null;
        }

        return [
            'width' => $width,
            'height' => $height,
            'bit_depth' => $bit_depth,
            'color_type' => $color_type,
            'idat' => $idat,
        ];
    }

    private function decode_png_scanlines(string $data, int $width, int $height, int $channels): ?string {
        $row_length = $width * $channels;
        $offset = 0;
        $previous = array_fill(0, $row_length, 0);
        $output = '';

        for($row = 0; $row < $height; $row++) {
            if($offset >= strlen($data)) {
                return null;
            }

            $filter = ord($data[$offset]);
            $offset++;
            $raw = substr($data, $offset, $row_length);
            $offset += $row_length;

            if(strlen($raw) !== $row_length) {
                return null;
            }

            $decoded = [];

            for($i = 0; $i < $row_length; $i++) {
                $value = ord($raw[$i]);
                $left = $i >= $channels ? $decoded[$i - $channels] : 0;
                $up = $previous[$i] ?? 0;
                $upper_left = $i >= $channels ? ($previous[$i - $channels] ?? 0) : 0;

                switch($filter) {
                    case 0:
                        $decoded[$i] = $value;
                        break;

                    case 1:
                        $decoded[$i] = ($value + $left) & 0xFF;
                        break;

                    case 2:
                        $decoded[$i] = ($value + $up) & 0xFF;
                        break;

                    case 3:
                        $decoded[$i] = ($value + floor(($left + $up) / 2)) & 0xFF;
                        break;

                    case 4:
                        $decoded[$i] = ($value + $this->paeth_predictor($left, $up, $upper_left)) & 0xFF;
                        break;

                    default:
                        return null;
                }
            }

            foreach($decoded as $byte) {
                $output .= chr($byte);
            }

            $previous = $decoded;
        }

        return $output;
    }

    private function paeth_predictor(int $left, int $up, int $upper_left): int {
        $p = $left + $up - $upper_left;
        $pa = abs($p - $left);
        $pb = abs($p - $up);
        $pc = abs($p - $upper_left);

        if($pa <= $pb && $pa <= $pc) {
            return $left;
        }

        return $pb <= $pc ? $up : $upper_left;
    }

    private function convert_image_to_jpeg(string $path, int $type): ?string {
        if(!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $source = null;

        if($type === IMAGETYPE_PNG && function_exists('imagecreatefrompng')) {
            $source = @imagecreatefrompng($path);
        } elseif($type === IMAGETYPE_GIF && function_exists('imagecreatefromgif')) {
            $source = @imagecreatefromgif($path);
        } elseif(defined('IMAGETYPE_WEBP') && $type === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $source = @imagecreatefromwebp($path);
        }

        if(!$source) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);

        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        ob_start();
        imagejpeg($canvas, null, 92);
        $data = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return $data ?: null;
    }

    private function extract_notes($payment, array $options): ?string {
        if(!empty($options['notes'])) {
            return (string) $options['notes'];
        }

        if(!empty($payment->billing->notes)) {
            return (string) $payment->billing->notes;
        }

        return null;
    }

    private function format_long_date($date): ?string {
        if(!$date) {
            return null;
        }

        try {
            $timezone = \Altum\Date::$timezone ?: date_default_timezone_get();
            return (new \DateTime($date))->setTimezone(new \DateTimeZone($timezone))->format('l, F jS, Y');
        } catch(\Exception $exception) {
            return null;
        }
    }

    private function format_period_range($start, $end): ?string {
        if(!$start || !$end) {
            return null;
        }

        try {
            $timezone = \Altum\Date::$timezone ?: date_default_timezone_get();
            $period_start = (new \DateTime($start))->setTimezone(new \DateTimeZone($timezone));
            $period_end = (new \DateTime($end))->setTimezone(new \DateTimeZone($timezone))->modify('-1 day');

            return '(' . $period_start->format('d/m/Y') . ' - ' . $period_end->format('d/m/Y') . ')';
        } catch(\Exception $exception) {
            return null;
        }
    }

    private function frequency_label(?string $frequency): ?string {
        if(!$frequency) {
            return null;
        }

        return $this->label('invoice.frequency.' . $frequency, ucwords($frequency));
    }

    private function processor_label(string $processor): string {
        $label = $this->label('pay.custom_plan.' . $processor, null);

        if($label && $label !== 'pay.custom_plan.' . $processor) {
            return $label;
        }

        return ucwords(str_replace(['_', '-'], ' ', $processor));
    }

    private function country_name(string $country): string {
        if(function_exists('get_countries_array')) {
            $countries = get_countries_array();
            return $countries[$country] ?? $country;
        }

        return $country;
    }

    private function label(string $key, ?string $fallback = null): string {
        if(function_exists('l')) {
            $label = l($key);
            return ($label && $label !== $key) ? $label : ($fallback ?? $key);
        }

        return $fallback ?? $key;
    }

    private function money(float $amount, string $currency): string {
        $formatted = function_exists('nr') ? nr($amount, 2) : number_format($amount, 2, '.', ',');
        return trim($formatted . ' ' . $currency);
    }

    private function wrap_text($text, float $max_width, float $size, bool $bold = false): array {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        if($text === '') {
            return [];
        }

        $lines = [];
        $line = '';
        $words = preg_split('/\s+/', $text);

        foreach($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;

            if($this->text_width($candidate, $size, $bold) <= $max_width) {
                $line = $candidate;
                continue;
            }

            if($line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $lines[] = $word;
            }
        }

        if($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    private function text_width($text, float $size, bool $bold = false): float {
        $text = $this->to_pdf_text($text);
        $factor = $bold ? 0.56 : 0.52;

        return strlen($text) * $size * $factor;
    }

    private function draw_text(float $x, float $top, $text, float $size = 10, string $font = 'F1', array $color = null): void {
        $text = (string) $text;

        if($text === '') {
            return;
        }

        $font_id = $font === 'F2' ? 'F2' : 'F1';
        $color = $color ?? $this->ink;
        $y = $this->page_height - $top - $size;

        $this->content .= 'BT /' . $font_id . ' ' . $this->num($size) . ' Tf ' . $this->rgb($color, 'fill') . ' ' . $this->num($x) . ' ' . $this->num($y) . ' Td (' . $this->escape_text($text) . ") Tj ET\n";
    }

    private function draw_text_right(float $right, float $top, $text, float $size = 10, string $font = 'F1', array $color = null): void {
        $bold = $font === 'F2';
        $x = $right - $this->text_width((string) $text, $size, $bold);
        $this->draw_text($x, $top, $text, $size, $font, $color);
    }

    private function draw_text_center(float $x, float $top, float $width, $text, float $size = 10, string $font = 'F1', array $color = null): void {
        $bold = $font === 'F2';
        $text_width = $this->text_width((string) $text, $size, $bold);
        $this->draw_text($x + (($width - $text_width) / 2), $top, $text, $size, $font, $color);
    }

    private function draw_rect(float $x, float $top, float $width, float $height, ?array $fill = null, ?array $stroke = null, float $line_width = 1): void {
        $y = $this->page_height - $top - $height;
        $operator = $fill && $stroke ? 'B' : ($fill ? 'f' : 'S');

        $this->content .= "q\n";
        $this->content .= $this->num($line_width) . " w\n";

        if($fill) {
            $this->content .= $this->rgb($fill, 'fill') . "\n";
        }

        if($stroke) {
            $this->content .= $this->rgb($stroke, 'stroke') . "\n";
        }

        $this->content .= $this->num($x) . ' ' . $this->num($y) . ' ' . $this->num($width) . ' ' . $this->num($height) . ' re ' . $operator . "\nQ\n";
    }

    private function draw_line(float $x1, float $top1, float $x2, float $top2, array $color, float $line_width = 1): void {
        $y1 = $this->page_height - $top1;
        $y2 = $this->page_height - $top2;

        $this->content .= "q\n";
        $this->content .= $this->num($line_width) . " w\n";
        $this->content .= $this->rgb($color, 'stroke') . "\n";
        $this->content .= $this->num($x1) . ' ' . $this->num($y1) . ' m ' . $this->num($x2) . ' ' . $this->num($y2) . " l S\nQ\n";
    }

    private function draw_image(string $name, float $x, float $top, float $width, float $height): void {
        $y = $this->page_height - $top - $height;
        $this->content .= "q\n";
        $this->content .= $this->num($width) . ' 0 0 ' . $this->num($height) . ' ' . $this->num($x) . ' ' . $this->num($y) . " cm\n";
        $this->content .= '/' . $name . " Do\nQ\n";
    }

    private function rgb(array $color, string $mode = 'fill'): string {
        $values = array_map(fn($value) => $this->num(max(0, min(255, (int) $value)) / 255), $color);
        return implode(' ', $values) . ($mode === 'stroke' ? ' RG' : ' rg');
    }

    private function escape_text($text): string {
        $text = $this->to_pdf_text($text);
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function to_pdf_text($text): string {
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);

        if(function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);

            if($converted !== false) {
                return $converted;
            }
        }

        return preg_replace('/[^\x20-\x7E]/', '', $text);
    }

    private function num(float $number): string {
        return number_format($number, 2, '.', '');
    }

    private function add_object(string $content): int {
        $this->objects[] = $content;
        return count($this->objects);
    }

    private function build_pdf(): string {
        $resources = '<< /Font << /F1 ' . $this->font_regular_id . ' 0 R /F2 ' . $this->font_bold_id . ' 0 R >>';

        if($this->logo) {
            $resources .= ' /XObject << /Im1 ' . $this->logo['id'] . ' 0 R >>';
        }

        $resources .= ' >>';

        $pages_id = count($this->objects) + 1;
        $page_id = $pages_id + 1;
        $content_id = $pages_id + 2;
        $catalog_id = $pages_id + 3;

        $this->add_object('<< /Type /Pages /Kids [' . $page_id . ' 0 R] /Count 1 >>');
        $this->add_object('<< /Type /Page /Parent ' . $pages_id . ' 0 R /MediaBox [0 0 ' . $this->num($this->page_width) . ' ' . $this->num($this->page_height) . '] /Resources ' . $resources . ' /Contents ' . $content_id . ' 0 R >>');
        $this->add_object("<< /Length " . strlen($this->content) . " >>\nstream\n" . $this->content . "\nendstream");
        $this->add_object('<< /Type /Catalog /Pages ' . $pages_id . ' 0 R >>');

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];

        foreach($this->objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xref_offset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($this->objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for($i = 1; $i <= count($this->objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($this->objects) + 1) . ' /Root ' . $catalog_id . " 0 R >>\n";
        $pdf .= "startxref\n" . $xref_offset . "\n%%EOF";

        return $pdf;
    }

}
