<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Models;

defined('ALTUMCODE') || die();

class QrCode extends Model {

    public function delete($qr_code_id) {

        if(!$qr_code = db()->where('qr_code_id', $qr_code_id)->getOne('qr_codes', ['user_id', 'qr_code_id', 'qr_code', 'qr_code_logo', 'qr_code_background'])) {
            return;
        }

        foreach(['qr_code', 'qr_code_logo', 'qr_code_background', 'qr_code_foreground'] as $image_key) {
            \Altum\Uploads::delete_uploaded_file($qr_code->{$image_key} ?? '', $image_key);
        }

        /* Delete from database */
        db()->where('qr_code_id', $qr_code_id)->delete('qr_codes');

        /* Clear the cache */
        cache()->deleteItem('qr_codes_total?user_id=' . $qr_code->user_id);
    }
}
