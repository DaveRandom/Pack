<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\PackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

abstract class NumericType implements ScalarType
{
    const LITTLE_ENDIAN = 0b01;

    private $width;
    private $specifier;
    private $reverse;

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

    protected function __construct(int $width, string $specifier, bool $reverse)
    {
        $this->width = $width;
        $this->specifier = $specifier;
        $this->reverse = $reverse;
    }
}
