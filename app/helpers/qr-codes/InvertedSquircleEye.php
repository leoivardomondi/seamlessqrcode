<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\QrCodes;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;
use SimpleSoftwareIO\QrCode\Singleton;

final class InvertedSquircleEye implements EyeInterface, Singleton
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
            ['move', [1.5, 6.0]],
            ['curve', [1.2, 5.3, 0.7, 4.8, 0.0, 4.5]],
            ['line',  [0.0, 1.5]],
            ['curve', [0.0, 0.7, 0.7, 0.0, 1.5, 0.0]],
            ['line',  [4.5, 0.0]],
            ['curve', [4.8, 0.7, 5.3, 1.2, 6.0, 1.5]],
            ['line',  [6.0, 4.5]],
            ['curve', [6.0, 5.3, 5.3, 6.0, 4.5, 6.0]],
            ['line',  [1.5, 6.0]]
        ];

        foreach ($commands as [$command, $coords]) {
            if($command === 'move' || $command === 'line') {
                [$x, $y] = $coords;
                $x = ($x - $offset) * $scale;
                $y = ($y - $offset) * $scale;
                $path = ($command === 'move') ? $path->move($x, $y) : $path->line($x, $y);
            }

            if($command === 'curve') {
                [$x1, $y1, $x2, $y2, $x, $y] = $coords;

                $x1 = ($x1 - $offset) * $scale;
                $y1 = ($y1 - $offset) * $scale;
                $x2 = ($x2 - $offset) * $scale;
                $y2 = ($y2 - $offset) * $scale;
                $x = ($x - $offset) * $scale;
                $y = ($y - $offset) * $scale;

                $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
            }
        }

        $path = $path->close();

        return $path;
    }
}
