<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use const DaveRandom\Pack\DOUBLE_WIDTH;
use const DaveRandom\Pack\FLOAT_WIDTH;
use const DaveRandom\Pack\HAVE_FLOAT_ORDER;
use const DaveRandom\Pack\SYSTEM_LITTLE_ENDIAN;

final class Float64 extends NumericType
{
    public function __construct(int $flags)
    {
        static $codeInfo = [];

        $littleEndian = (bool)($flags & self::LITTLE_ENDIAN);
        $cacheKey = (int)$littleEndian;

        if (!isset($codeInfo[$cacheKey])) {
            if (FLOAT_WIDTH === 64) {
                if (HAVE_FLOAT_ORDER) {
                    $specifier = ['G', 'g'][$cacheKey];
                    $reverse = false;
                } else {
                    $specifier = 'f';
                    $reverse = $littleEndian !== SYSTEM_LITTLE_ENDIAN;
                }
            } else if (DOUBLE_WIDTH === 64) {
                if (HAVE_FLOAT_ORDER) {
                    $specifier = ['E', 'e'][$cacheKey];
                    $reverse = false;
                } else {
                    $specifier = 'd';
                    $reverse = $littleEndian !== SYSTEM_LITTLE_ENDIAN;
                }
            } else {
                throw new \RuntimeException('System does not have a 64-bit float representation');
            }

            $codeInfo[$cacheKey] = [$specifier, $reverse];
        } else {
            list($specifier, $reverse) = $codeInfo[$cacheKey];
        }

        parent::__construct(64, $specifier, $reverse);
    }
}
