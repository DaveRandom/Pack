<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\TypeCodes;

abstract class FloatType extends NumericType
{
    private function getSpecifierAndReversalInfo(int $width, bool $isLittleEndian)
    {
        if (\DaveRandom\Pack\SYSTEM_FLOAT_WIDTH === $width) {
            return \DaveRandom\Pack\HAVE_FLOAT_ORDER
                ? [[TypeCodes::FLOAT, TypeCodes::FLOAT_LE][(int)$isLittleEndian], false]
                : [TypeCodes::FLOAT_SYS, $isLittleEndian !== \DaveRandom\Pack\SYSTEM_LITTLE_ENDIAN];
        }

        if (\DaveRandom\Pack\SYSTEM_DOUBLE_WIDTH === $width) {
            return \DaveRandom\Pack\HAVE_FLOAT_ORDER
                ? [[TypeCodes::DOUBLE, TypeCodes::DOUBLE_LE][(int)$isLittleEndian], false]
                : [TypeCodes::DOUBLE_SYS, $isLittleEndian !== \DaveRandom\Pack\SYSTEM_LITTLE_ENDIAN];
        }

        throw new \RuntimeException("System does not have a {$width}-bit float representation");
    }

    protected function __construct(int $width, bool $isLittleEndian)
    {
        parent::__construct($width, ...$this->getSpecifierAndReversalInfo($width, $isLittleEndian));
    }
}
