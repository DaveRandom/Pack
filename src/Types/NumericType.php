<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use DaveRandom\Pack\TypeCodes;
use const DaveRandom\Pack\UNBOUNDED;

abstract class NumericType implements ScalarType
{
    const LITTLE_ENDIAN = 0b01;

    private static $specifierSizes = [
        TypeCodes::INT_SYS    => \DaveRandom\Pack\INT_SIZE,
        TypeCodes::UINT_SYS   => \DaveRandom\Pack\INT_SIZE,

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

        TypeCodes::FLOAT      => \DaveRandom\Pack\FLOAT_SIZE,
        TypeCodes::FLOAT_LE   => \DaveRandom\Pack\FLOAT_SIZE,
        TypeCodes::FLOAT_SYS  => \DaveRandom\Pack\FLOAT_SIZE,

        TypeCodes::DOUBLE     => \DaveRandom\Pack\DOUBLE_SIZE,
        TypeCodes::DOUBLE_LE  => \DaveRandom\Pack\DOUBLE_SIZE,
        TypeCodes::DOUBLE_SYS => \DaveRandom\Pack\DOUBLE_SIZE,
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

    public function generatePackCodeForExpression(PackCompilationContext $ctx, string $expr)
    {
        if ($this->reverse) {
            $ctx->appendResult("\strrev(\pack('{$this->specifier}', {$expr}))");
        } else {
            $ctx->appendSpecifier($this->specifier, null, $expr);
        }
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if (!$this->reverse) {
            $ctx->appendSpecifier($this->specifier, $count);
            return;
        }

        $arg = $ctx->getCurrentArg();
        $size = $this->width / 8;

        if ($count === null) {
            $ctx->appendResult("\strrev(\pack('{$this->specifier}', {$arg}))");
        } else if ($count === UNBOUNDED) {
            $ctx->appendResult("\implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}*', ...{$arg}), {$size})))");
        } else {
            $ctx->appendResult("\implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}{$count}', {$ctx->getCurrentArgAsBoundedArrayArgList($count)}), {$size})))");
        }
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
