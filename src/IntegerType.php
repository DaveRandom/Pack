<?php declare(strict_types=1);

namespace DaveRandom\Pack;

abstract class IntegerType implements Element
{
    const UNSIGNED = 0b01;
    const LITTLE_ENDIAN = 0b10;

    private $width;
    private $specifier;
    private $builtIn = true;

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($this->builtIn) {
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

    public function __construct(int $width, int $flags = 0)
    {
        if (!\array_key_exists($width, INT_BY_SIZE)) {
            throw new \InvalidArgumentException("Invalid integer size: {$width}");
        }

        $isUnsigned = (bool)($flags & self::UNSIGNED);
        $isLittleEndian = (bool)($flags & self::LITTLE_ENDIAN);

        // Unsigned integers can all use built-in formats
        if ($isUnsigned) {
            $this->specifier = ($isLittleEndian && $width !== 8 ? UINT_LE_BY_SIZE : UINT_BY_SIZE)[$width];
            return;
        }

        $this->specifier = INT_BY_SIZE[$width];

        // Signed char and signed integers where the endianness matches the system can use built-in formats
        if ($width === 8 || $isLittleEndian === SYSTEM_LITTLE_ENDIAN) {
            return;
        }

        // Signed integers that do not match system endianness must use generated code
        $this->builtIn = false;
        $this->width = $width;
    }
}
