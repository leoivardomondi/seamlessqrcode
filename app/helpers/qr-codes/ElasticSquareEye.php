<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\QrCodes;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;
use SimpleSoftwareIO\QrCode\Singleton;

final class ElasticSquareEye implements EyeInterface, Singleton
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
    }

    public function getInternalPath(): Path
    {
        $path = new Path();

        $scale = 0.5;
        $offset = 3.0;

        $commands = [
            ['move',  [6.0, 6.0]],
            ['curve', [4.1, 5.4, 1.9, 5.4, 0.0, 6.0]],

            ['curve', [0.6, 4.1, 0.6, 1.9, 0.0, 0.0]],

            ['curve', [1.9, 0.6, 4.1, 0.6, 6.0, 0.0]],

            ['curve', [5.4, 1.9, 5.4, 4.1, 6.0, 6.0]]
        ];

        foreach ($commands as [$command, $coords]) {
            if($command === 'move') {
                [$x, $y] = $coords;
                $x = ($x - $offset) * $scale;
                $y = ($y - $offset) * $scale;
                $path = $path->move($x, $y);
            }

            if($command === 'curve') {
                [$x1, $y1, $x2, $y2, $x, $y] = $coords;

                $x1 = ($x1 - $offset) * $scale;
                $y1 = ($y1 - $offset) * $scale;
                $x2 = ($x2 - $offset) * $scale;
                $y2 = ($y2 - $offset) * $scale;
                $x  = ($x  - $offset) * $scale;
                $y  = ($y  - $offset) * $scale;

                $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
            }
        }

        $path = $path->close();

        return $path;
    }
}
