<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\QrCodes;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;
use SimpleSoftwareIO\QrCode\Singleton;

final class ThickStarEye implements EyeInterface, Singleton
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

    public function getInternalPath() : Path
    {
        $path = new Path();

        /* convert SVG‑like 0‑6 grid → QR eye −3..3  */
        $scale_factor  = 0.6;  /* enlarge / shrink the whole gear here  */
        $center_offset = 3.0;

        $convert = static function (float $x_coordinate, float $y_coordinate) use ($scale_factor, $center_offset): array {
            return [
                ($x_coordinate - $center_offset) * $scale_factor,
                ($y_coordinate - $center_offset) * $scale_factor,
            ];
        };

        /* 12 points → 6 outer teeth + 6 inner roots  (start at 0°, then 30°, 60° … clockwise) */
        $raw_points = [
            /* outer tooth 0°  */ [5.5, 3.0],
            /* inner root 30° */ [4.56, 3.90],
            /* outer tooth 60° */ [4.25, 5.17],
            /* inner root 90° */ [3.00, 4.80],
            /* outer tooth 120°*/ [1.75, 5.17],
            /* inner root 150°*/ [1.44, 3.90],
            /* outer tooth 180°*/ [0.50, 3.00],
            /* inner root 210°*/ [1.44, 2.10],
            /* outer tooth 240°*/ [1.75, 0.83],
            /* inner root 270°*/ [3.00, 1.20],
            /* outer tooth 300°*/ [4.25, 0.83],
            /* inner root 330°*/ [4.56, 2.10],
        ];

        $is_first_point = true;
        foreach ($raw_points as [$x_coordinate, $y_coordinate]) {
            [$normalised_x, $normalised_y] = $convert($x_coordinate, $y_coordinate);

            $path = $is_first_point
                ? $path->move($normalised_x, $normalised_y)
                : $path->line($normalised_x, $normalised_y);

            $is_first_point = false;
        }

        return $path->close();
    }
}
