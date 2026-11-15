<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\QrCodes;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;
use SimpleSoftwareIO\QrCode\Singleton;

final class HexagonEye implements EyeInterface, Singleton
{
    private static $instance;

    private function __construct()
    {
    }

    public static function instance() : self
    {
        return self::$instance ?: self::$instance = new self();
    }

    public function getExternalPath(): Path
    {
        $path = new Path();

        $outer_radius = 3.5;
        $inner_radius = 2.5;
        $sides = 6;

        /* outer hexagon */
        for ($i = 0; $i < $sides; $i++) {
            $angle = deg2rad(60 * $i - 30); /* flat-top hexagon */
            $x = cos($angle) * $outer_radius;
            $y = sin($angle) * $outer_radius;

            if($i === 0) {
                $path = $path->move($x, $y);
            } else {
                $path = $path->line($x, $y);
            }
        }
        $path = $path->close();

        /* inner hexagon (cutout) */
        for ($i = 0; $i < $sides; $i++) {
            $angle = deg2rad(60 * $i - 30); /* same angle */
            $x = cos($angle) * $inner_radius;
            $y = sin($angle) * $inner_radius;

            if($i === 0) {
                $path = $path->move($x, $y);
            } else {
                $path = $path->line($x, $y);
            }
        }
        $path = $path->close();

        return $path;
    }

    public function getInternalPath(): Path
    {
        $path = new Path();

        $radius = 1.5;
        $sides = 6;

        for ($i = 0; $i < $sides; $i++) {
            $angle = deg2rad(60 * $i - 30); /* flat-top hexagon */
            $x = cos($angle) * $radius;
            $y = sin($angle) * $radius;

            if($i === 0) {
                $path = $path->move($x, $y);
            } else {
                $path = $path->line($x, $y);
            }
        }

        return $path->close();
    }
}
