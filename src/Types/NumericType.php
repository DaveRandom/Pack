<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\PackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

abstract class NumericType implements Type
{
    const LITTLE_ENDIAN = 0b01;

    private $width;
    private $specifier;
    private $reverse;

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if (!$this->reverse) {
            $ctx->appendSpecifier($this->specifier, $count);
            return;
        }

        $arg = $ctx->getCurrentArg();

        if ($count === null) {
            $ctx->appendResult("\strrev(\pack('{$this->specifier}', {$arg}))");
            return;
        }

        $size = $this->width / 8;

        if ($count === UNBOUNDED) {
            $ctx->appendResult("\implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}*', ...{$arg}), {$size})))");
            return;
        }

        $args = [];

        for ($i = 0; $i < $count; $i++) {
            $args[] = "{$arg}[{$i}]";;
        }

        $args = \implode(', ', $args);

        $ctx->appendResult("\implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}{$count}', {$args}), {$size})))");
    }

    /*
    public function generateUnpackCode(string $name): array
    {
        $specifier = INT_BY_SIZE[$this->size];
        $unpackKey = \var_export($name, true);
        $byteCount = $this->size / 8;

        if ($this->quantity === 1) {
            return [
                "\$result[{$unpackKey}] = \unpack('{$specifier}', \strrev(\substr(\$data, \$offset, {$byteCount})))[1];",
                "\$offset += {$byteCount};",
            ];
        }

        if ($this->quantity === Element::REPEAT) {
            return [
                "\$result[{$unpackKey}] = \array_values(\unpack('{$specifier}*', \implode('', \array_map('strrev', \str_split(\substr(\$data, \$offset), {$byteCount})))));",
                "\$offset = \$length - ((\$length - \$offset) % {$byteCount});",
            ];
        }

        $length = $byteCount * $this->quantity;

        return [
            "\$result[{$unpackKey}] = \array_values(\unpack('{$specifier}{$this->quantity}', \implode('', \array_map('strrev', \str_split(\substr(\$data, \$offset, {$length}), {$byteCount})))));",
            "\$offset += {$length};",
        ];
    }
    */

    protected function __construct(int $width, string $specifier, bool $reverse)
    {
        $this->width = $width;
        $this->specifier = $specifier;
        $this->reverse = $reverse;
    }
}
