<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;
use const DaveRandom\Pack\UNBOUNDED;

final class SpacePaddedString implements VectorType
{
    private $length;

    public function __construct(int $length)
    {
        $this->length = $length;
    }

    public function generatePackCode(PackMethod $method, int $count = null)
    {
        $arg = $method->getCurrentArg();
        $specifier = "A{$this->length}";

        if ($count === null) {
            $method->appendPackSpecifier($specifier);
            return;
        }

        if ($count === UNBOUNDED) {
            $method->appendResult("\implode('', \array_map(function(\$s) { return \pack('{$specifier}', \$s); }, {$arg}))");
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $method->appendPackSpecifier($specifier, null, "{$arg}[{$i}]");
        }
    }

    public function generateUnpackCode(UnpackMethod $method, int $count = null)
    {
        $specifier = "A{$this->length}";

        if ($count === null) {
            $method->appendUnpackSpecifier($specifier, $this->length, $count);
            return;
        }

        if ($count === UNBOUNDED) {
            $method->appendResultWithCount("\array_map(function(\$s) { return \unpack('{$specifier}', \$s)[1]; }, \str_split(\substr({$method->getData()}, {$method->getOffset()}), {$this->length}))", $this->length);
            return;
        }

        $length = $this->length * $count;
        $method->appendResult("\array_map(function(\$s) { return \unpack('{$specifier}', \$s)[1]; }, \str_split(\substr({$method->getData()}, {$method->getOffset()}, {$length}), {$this->length}))", $length);
    }

    public function isFixedSize(): bool
    {
        return true;
    }

    public function getSize(): int
    {
        return $this->length;
    }

    public function count(): int
    {
        return $this->length;
    }

    public function isFinite(): bool
    {
        return $this->length !== UNBOUNDED;
    }
}
