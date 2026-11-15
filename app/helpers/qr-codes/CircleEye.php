<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\QrCodes;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;
use SimpleSoftwareIO\QrCode\Singleton;

final class CircleEye implements EyeInterface, Singleton
{
    private static $instance;

    private function __construct()
    {
    }

    public static function instance() : self
    {
        return self::$instance ?: self::$instance = new self();
    }

    public function getExternalPath() : Path
    {
        return (new Path())
            ->move(3.5, 0)
            ->ellipticArc(3.5, 3.5, 0., false, true, 0., 3.5)
            ->ellipticArc(3.5, 3.5, 0., false, true, -3.5, 0.)
            ->ellipticArc(3.5, 3.5, 0., false, true, 0., -3.5)
            ->ellipticArc(3.5, 3.5, 0., false, true, 3.5, 0.)
            ->close()
            ->move(2.5, 0)
            ->ellipticArc(2.5, 2.5, 0., false, true, 0., 2.5)
            ->ellipticArc(2.5, 2.5, 0., false, true, -2.5, 0.)
            ->ellipticArc(2.5, 2.5, 0., false, true, 0., -2.5)
            ->ellipticArc(2.5, 2.5, 0., false, true, 2.5, 0.)
            ->close()
            ;
    }

    public function getInternalPath() : Path
    {
        return (new Path())
            ->move(1.5, 0)
            ->ellipticArc(1.5, 1.5, 0., false, true, 0., 1.5)
            ->ellipticArc(1.5, 1.5, 0., false, true, -1.5, 0.)
            ->ellipticArc(1.5, 1.5, 0., false, true, 0., -1.5)
            ->ellipticArc(1.5, 1.5, 0., false, true, 1.5, 0.)
            ->close()
            ;
    }
}
