<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use const DaveRandom\Pack\INT_CODES_BY_WIDTH;
use const DaveRandom\Pack\SYSTEM_LITTLE_ENDIAN;
use const DaveRandom\Pack\UINT_CODES_BY_WIDTH;
use const DaveRandom\Pack\UINT_LE_CODES_BY_WIDTH;

abstract class IntegerType extends NumericType
{
    const UNSIGNED = 0b10;

    protected function __construct(int $width, int $flags = 0)
    {
        if (!\array_key_exists($width, INT_CODES_BY_WIDTH)) {
            throw new \InvalidArgumentException("Invalid integer width: {$width}");
        }

        $isUnsigned = (bool)($flags & self::UNSIGNED);
        $isLittleEndian = (bool)($flags & self::LITTLE_ENDIAN);

        $specifier = $isUnsigned
            ? ($isLittleEndian && $width !== 8 ? UINT_LE_CODES_BY_WIDTH : UINT_CODES_BY_WIDTH)[$width]
            : INT_CODES_BY_WIDTH[$width];

        // Only signed multi-byte integers that do not match the system endianness need to be reversed
        $reverse = $isLittleEndian !== SYSTEM_LITTLE_ENDIAN
            && !$isUnsigned
            && $width !== 8;

        parent::__construct($width, $specifier, $reverse);
    }
}
