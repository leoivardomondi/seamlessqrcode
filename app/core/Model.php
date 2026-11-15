<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Models;

use Altum\Traits\Paramsable;

defined('ALTUMCODE') || die();

class Model {
    use Paramsable;

    public $model;

    public function __construct(Array $params = []) {

        $this->add_params($params);

    }

}
