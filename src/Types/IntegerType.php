<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\TypeCodes;

abstract class IntegerType extends NumericType
{
    const UNSIGNED = 0b10;

    private static $intCodesByWidth = [
        8  => TypeCodes::INT8,
        16 => TypeCodes::INT16_SYS,
        32 => TypeCodes::INT32_SYS,
        64 => TypeCodes::INT64_SYS,
    ];

    private static $uintCodesByWidth = [
        8  => TypeCodes::UINT8,
        16 => TypeCodes::UINT16,
        32 => TypeCodes::UINT32,
        64 => TypeCodes::UINT64,
    ];

    private static $uintLeCodesByWidth = [
        16 => TypeCodes::UINT16_LE,
        32 => TypeCodes::UINT32_LE,
        64 => TypeCodes::UINT64_LE,
    ];

    protected function __construct(int $width, int $flags = 0)
    {
        if (!\array_key_exists($width, self::$intCodesByWidth)) {
            throw new \InvalidArgumentException("Invalid integer width: {$width}");
        }

        $isUnsigned = (bool)($flags & self::UNSIGNED);
        $isLittleEndian = (bool)($flags & self::LITTLE_ENDIAN);

        $specifier = $isUnsigned
            ? ($isLittleEndian && $width !== 8 ? self::$uintLeCodesByWidth : self::$uintCodesByWidth)[$width]
            : self::$intCodesByWidth[$width];

        // Only signed multi-byte integers that do not match the system endianness need to be reversed
        $reverse = $isLittleEndian !== \DaveRandom\Pack\SYSTEM_LITTLE_ENDIAN
            && !$isUnsigned
            && $width !== 8;

        parent::__construct($width, $specifier, $reverse);
    }
}
