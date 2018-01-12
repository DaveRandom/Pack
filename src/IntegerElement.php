<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class IntegerElement implements Element
{
    const SIGNED = 0b01;
    const LITTLE_ENDIAN = 0b10;

    private static $repeaters = [
        1 => '',
        Element::REPEAT => '*',
    ];

    private $name;
    private $size;
    private $quantity;
    private $builtIn;
    private $packFormat;
    private $unpackFormat;
    private $packCode;
    private $unpackCode;

    private function initializeUnsigned(int $size, int $quantity, bool $isLittleEndian)
    {
        $this->builtIn = true;
        $specifier = ($isLittleEndian && $size !== 8 ? UINT_LE_BY_SIZE : UINT_BY_SIZE)[$size];
        $repeater = self::$repeaters[$quantity] ?? $quantity;
        $this->packFormat = "{$specifier}{$repeater}";
        $this->unpackFormat = "{$specifier}{$this->name}{$repeater}";
    }

    private function initializeBuiltInSigned(int $size, int $quantity)
    {
        $this->builtIn = true;
        $specifier = INT_BY_SIZE[$size];
        $repeater = self::$repeaters[$quantity] ?? $quantity;
        $this->packFormat = "{$specifier}{$repeater}";
        $this->unpackFormat = "{$specifier}{$this->name}{$repeater}";
    }

    private function generatePackCode(): string
    {
        if ($this->quantity === 1) {
            return "\$result .= \strrev(\pack('" . INT_BY_SIZE[$this->size] . "', \$args[\$argIndex++]));";
        }

        if ($this->quantity === Element::REPEAT) {
            return "\$result .= \implode('', \array_map('strrev', \str_split(\pack('" . INT_BY_SIZE[$this->size] . "*', ...\array_slice(\$args, \$argIndex)))));";
        }

        $specifier = INT_BY_SIZE[$this->size] . $this->quantity;
        $byteCount = $this->size / 8;

        $args = ['$args[$argIndex]'];

        for ($i = 1; $i < $this->quantity; $i++) {
            $args[] = '$args[$argIndex + ' . $i . ']';
        }

        $args = \implode(', ', $args);

        return <<<CODE
\$result .= \implode('', \array_map('strrev', \str_split(\pack('{$specifier}', {$args}), {$byteCount})));
\$argIndex += {$this->quantity};
CODE;
    }

    private function generateUnpackCode(): string
    {
        $specifier = INT_BY_SIZE[$this->size];
        $unpackKey = \var_export($this->name, true);
        $byteCount = $this->size / 8;

        if ($this->quantity === 1) {
            return <<<CODE
\$result[{$unpackKey}] = \unpack('{$specifier}', \strrev(\substr(\$data, \$offset, {$byteCount})))[1];
\$offset += {$byteCount};
CODE;
        }

        if ($this->quantity === Element::REPEAT) {
            return <<<CODE
\$result[{$unpackKey}] = \array_values(\unpack('{$specifier}*', \implode('', \array_map('strrev', \str_split(\substr(\$data, \$offset), {$byteCount})))));
CODE;
        }

        $length = $byteCount * $this->quantity;

        return <<<CODE
\$result[{$unpackKey}] = \array_values(\unpack('{$specifier}{$this->quantity}', \implode('', \array_map('strrev', \str_split(\substr(\$data, \$offset, {$length}), {$byteCount})))));
\$offset += {$length};
CODE;
    }

    public function __construct(string $name, int $size, int $quantity = 1, int $flags = 0)
    {
        if (!\preg_match('/^[\w\-.]+$/i', $name)) {
            throw new \InvalidArgumentException("Invalid element name: {$name}");
        }

        if (!\array_key_exists($size, INT_BY_SIZE)) {
            throw new \InvalidArgumentException("Invalid integer size: {$size}");
        }

        if ($quantity < 1 && $quantity !== Element::REPEAT) {
            throw new \InvalidArgumentException("Quantity must be a positive number greater than zero");
        }

        $this->name = $name;

        $isSigned = (bool)($flags & self::SIGNED);
        $isLittleEndian = (bool)($flags & self::LITTLE_ENDIAN);

        // Unsigned integers can all use built-in formats
        if (!$isSigned) {
            $this->initializeUnsigned($size, $quantity, $isLittleEndian);
            return;
        }

        // Signed char and signed integers where the endianness matches the system can use built-in formats
        if ($size === 8 || $isLittleEndian === SYSTEM_LITTLE_ENDIAN) {
            $this->initializeBuiltInSigned($size, $quantity);
            return;
        }

        // Signed integers that do not match system endianness must use generated code
        $this->builtIn = false;
        $this->size = $size;
        $this->quantity = $quantity;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isBuiltIn(): bool
    {
        return $this->builtIn;
    }

    public function getPackFormat(): string
    {
        if (!$this->builtIn) {
            throw new \LogicException('Cannot retrieve pack format for element that does not use built-in types');
        }

        return $this->packFormat;
    }

    public function getUnpackFormat(): string
    {
        if (!$this->builtIn) {
            throw new \LogicException('Cannot retrieve unpack format for element that does not use built-in types');
        }

        return $this->unpackFormat;
    }

    public function getPackCode(): string
    {
        if ($this->builtIn) {
            throw new \LogicException('Cannot retrieve pack code for element that uses built-in types');
        }

        return $this->packCode ?? ($this->packCode = $this->generatePackCode());
    }

    public function getUnpackCode(): string
    {
        if ($this->builtIn) {
            throw new \LogicException('Cannot retrieve unpack code for element that uses built-in types');
        }

        return $this->unpackCode ?? ($this->unpackCode = $this->generateUnpackCode());
    }
}
