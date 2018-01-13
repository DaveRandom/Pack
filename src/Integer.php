<?php declare(strict_types=1);

namespace DaveRandom\Pack;

abstract class Integer implements Element
{
    const SIGNED = 0b01;
    const LITTLE_ENDIAN = 0b10;

    private $width;
    private $specifier;
    private $builtIn = true;

    public function generatePackCode(PackCompilationContext $ctx, int $count)
    {
        if ($this->builtIn) {
            $ctx->appendSpecifier($this->specifier, $count);
            return;
        }

        if ($count === 1) {
            $ctx->appendCode("\$result {$ctx->getAssignmentOperator()} \strrev(\pack('{$this->specifier}', \$args[{$ctx->consumeArg()}]));");
            return;
        }

        if ($count === Element::REPEAT) {
            $ctx->appendCode("\$result {$ctx->getAssignmentOperator()} \implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}*', ...\array_slice(\$args, {$ctx->consumeArg()})))));");
            return;
        }

        $this->specifier .= $count;
        $size = $this->width / 8;

        $args = [];

        for ($i = 0; $i < $count; $i++) {
            $args[] = "\$args[{$ctx->consumeArg()}]";
        }

        $args = \implode(', ', $args);

        $ctx->appendCode("\$result {$ctx->getAssignmentOperator()} \implode('', \array_map('strrev', \str_split(\pack('{$this->specifier}', {$args}), {$size})));");
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

        $isSigned = (bool)($flags & self::SIGNED);
        $isLittleEndian = (bool)($flags & self::LITTLE_ENDIAN);

        // Unsigned integers can all use built-in formats
        if (!$isSigned) {
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
