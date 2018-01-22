<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Statement;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;
use DaveRandom\Pack\TypeCodes;
use const DaveRandom\Pack\UNBOUNDED;

abstract class NumericType implements ScalarType
{
    const LITTLE_ENDIAN = 0b01;
    const SYSTEM_TYPE_DEFAULT_FLAGS = \DaveRandom\Pack\SYSTEM_LITTLE_ENDIAN ? self::LITTLE_ENDIAN : 0;

    private static $specifierSizes = [
        TypeCodes::INT_SYS    => \DaveRandom\Pack\SYSTEM_INT_SIZE,
        TypeCodes::UINT_SYS   => \DaveRandom\Pack\SYSTEM_INT_SIZE,

        TypeCodes::INT8       => 1,
        TypeCodes::UINT8      => 1,

        TypeCodes::INT16_SYS  => 2,
        TypeCodes::UINT16     => 2,
        TypeCodes::UINT16_LE  => 2,
        TypeCodes::UINT16_SYS => 2,

        TypeCodes::INT32_SYS  => 4,
        TypeCodes::UINT32     => 4,
        TypeCodes::UINT32_LE  => 4,
        TypeCodes::UINT32_SYS => 4,

        TypeCodes::INT64_SYS  => 8,
        TypeCodes::UINT64     => 8,
        TypeCodes::UINT64_LE  => 8,
        TypeCodes::UINT64_SYS => 8,

        TypeCodes::FLOAT      => \DaveRandom\Pack\SYSTEM_FLOAT_SIZE,
        TypeCodes::FLOAT_LE   => \DaveRandom\Pack\SYSTEM_FLOAT_SIZE,
        TypeCodes::FLOAT_SYS  => \DaveRandom\Pack\SYSTEM_FLOAT_SIZE,

        TypeCodes::DOUBLE     => \DaveRandom\Pack\SYSTEM_DOUBLE_SIZE,
        TypeCodes::DOUBLE_LE  => \DaveRandom\Pack\SYSTEM_DOUBLE_SIZE,
        TypeCodes::DOUBLE_SYS => \DaveRandom\Pack\SYSTEM_DOUBLE_SIZE,
    ];

    private $width;
    private $specifier;
    private $reverse;

    protected function __construct(int $width, string $specifier, bool $reverse)
    {
        $this->width = $width;
        $this->specifier = $specifier;
        $this->reverse = $reverse;
    }

    public function generatePackCodeForExpression(PackMethod $method, string $expr)
    {
        if (!$this->reverse) {
            $method->appendPackSpecifier($this->specifier, null, $expr);
            return;
        }

        $method->appendResult("\strrev(\pack('{$this->specifier}', {$expr}))");
    }

    public function generateUnpackCodeForSingleValueAtCurrentOffset(UnpackMethod $method, string $target)
    {
        $method->appendLengthCheck($this->getSize());
        $method->appendCodeElements(new Statement("{$target} = \unpack('{$this->specifier}', {$method->getData()}, {$method->getOffset()})[1];"));
        $method->advanceDataOffset($this->getSize());
        $method->addSize($this->getSize());
    }

    public function generatePackCode(PackMethod $method, int $count = null)
    {
        if (!$this->reverse) {
            $method->appendPackSpecifier($this->specifier, $count);
            return;
        }

        $arg = $method->getCurrentArg();
        $size = $this->width / 8;

        if ($count === null) {
            $method->appendResult("\strrev(\pack('{$this->specifier}', {$arg}))");
            return;
        }

        if ($count === UNBOUNDED) {
            $method->appendResult("\implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}*', ...{$arg}), {$size})))");
            return;
        }

        $method->appendResult("\implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}{$count}', {$method->getCurrentArgAsBoundedArrayArgList($count)}), {$size})))");
    }

    public function generateUnpackCode(UnpackMethod $method, int $count = null)
    {
        $size = $this->width / 8;

        if (!$this->reverse) {
            $method->appendUnpackSpecifier($this->specifier, $size, $count);
            return;
        }

        if ($count === null) {
            $method->appendResult("\unpack('{$this->specifier}', \strrev(\substr({$method->getData()}, {$method->getOffset()}, {$size})))[1]", $size);
            return;
        }

        if ($count === UNBOUNDED) {
            $method->appendResultWithCount("\array_values(\unpack('{$this->specifier}*', \implode('', \array_map('strrev', \str_split(\substr({$method->getData()}, {$method->getOffset()}), {$size})))))", $size);
            return;
        }

        $length = $size * $count;
        $method->appendResult("\array_values(\unpack('{$this->specifier}{$count}', \implode('', \array_map('strrev', \str_split(\substr({$method->getData()}, {$method->getOffset()}, {$length}), {$size})))))", $length);
    }

    public function isFixedSize(): bool
    {
        return true;
    }

    public function getSize(): int
    {
        return self::$specifierSizes[$this->specifier];
    }
}
