<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\QrCodes;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;
use SimpleSoftwareIO\QrCode\Singleton;

final class ShineEye implements EyeInterface, Singleton
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

    public function getInternalPath() : Path
    {
        $path = new Path();

        $scale = 0.5;
        $offset = 3.0;

        $commands = [
            ['move',  [3.0, -1.0]],
            ['curve', [3.0, 1.2, 4.8, 3.0, 7.0, 3.0]],
            ['curve', [4.8, 3.0, 3.0, 4.8, 3.0, 7.0]],
            ['curve', [3.0, 4.8, 1.2, 3.0, -1.0, 3.0]],
            ['curve', [1.2, 3.0, 3.0, 1.2, 3.0, -1.0]],
        ];

        foreach ($commands as [$command, $coords]) {
            $x1 = $x2 = $x = $y1 = $y2 = $y = null;

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
