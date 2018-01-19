<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use DaveRandom\Pack\Compilation\Unpack\CompilationContext as UnpackCompilationContext;
use DaveRandom\Pack\Compilation\Unpack\Statement;
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

    public function generatePackCodeForExpression(PackCompilationContext $ctx, string $expr)
    {
        if (!$this->reverse) {
            $ctx->appendPackSpecifier($this->specifier, null, $expr);
            return;
        }

        $ctx->appendResult("\strrev(\pack('{$this->specifier}', {$expr}))");
    }

    public function generateUnpackCodeForSingleValueAtCurrentOffset(UnpackCompilationContext $ctx, string $target)
    {
        $ctx->appendCodeElements(new Statement("{$target} = \unpack('{$this->specifier}', {$ctx->getData()}, {$ctx->getOffset()});"));
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if (!$this->reverse) {
            $ctx->appendPackSpecifier($this->specifier, $count);
            return;
        }

        $arg = $ctx->getCurrentArg();
        $size = $this->width / 8;

        if ($count === null) {
            $ctx->appendResult("\strrev(\pack('{$this->specifier}', {$arg}))");
            return;
        }

        if ($count === UNBOUNDED) {
            $ctx->appendResult("\implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}*', ...{$arg}), {$size})))");
            return;
        }

        $ctx->appendResult("\implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}{$count}', {$ctx->getCurrentArgAsBoundedArrayArgList($count)}), {$size})))");
    }

    public function generateUnpackCode(UnpackCompilationContext $ctx, int $count = null)
    {
        $size = $this->width / 8;

        if (!$this->reverse) {
            $ctx->appendUnpackSpecifier($this->specifier, $size, $count);
            return;
        }

        if ($count === null) {
            $ctx->appendResult("\unpack('{$this->specifier}', \strrev(\substr({$ctx->getData()}, {$ctx->getOffset()}, {$size})))[1]", $size);
            return;
        }

        if ($count === UNBOUNDED) {
            $ctx->appendResultWithCount("\array_values(\unpack('{$this->specifier}*', \implode('', \array_map('strrev', \str_split(\substr({$ctx->getData()}, {$ctx->getOffset()}), {$size})))))", $size);
            return;
        }

        $length = $size * $count;
        $ctx->appendResult("\array_values(\unpack('{$this->specifier}{$count}', \implode('', \array_map('strrev', \str_split(\substr({$ctx->getData()}, {$ctx->getOffset()}, {$length}), {$size})))))", $length);
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
