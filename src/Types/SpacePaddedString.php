<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use DaveRandom\Pack\Compilation\Unpack\CompilationContext as UnpackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class SpacePaddedString implements VectorType
{
    private $length;

    public function __construct(int $length)
    {
        $this->length = $length;
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        $arg = $ctx->getCurrentArg();
        $specifier = "A{$this->length}";

        if ($count === null) {
            $ctx->appendPackSpecifier($specifier);
            return;
        }

        if ($count === UNBOUNDED) {
            $ctx->appendResult("\implode('', \array_map(function(\$s) { return \pack('{$specifier}', \$s); }, {$arg}))");
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $ctx->appendPackSpecifier($specifier, null, "{$arg}[{$i}]");
        }
    }

    public function generateUnpackCode(UnpackCompilationContext $ctx, int $count = null)
    {
        $specifier = "A{$this->length}";

        if ($count === null) {
            $ctx->appendUnpackSpecifier($specifier, $this->length, $count);
            return;
        }

        if ($count === UNBOUNDED) {
            $ctx->appendResultWithCount("\array_map(function(\$s) { return \unpack('{$specifier}', \$s); }, \str_split(\substr({$ctx->getData()}, {$ctx->getOffset()}), {$this->length}))", $this->length);
            return;
        }

        $length = $this->length * $count;
        $ctx->appendResult("\array_map(function(\$s) { return \unpack('{$specifier}', \$s); }, \str_split(\substr({$ctx->getData()}, {$ctx->getOffset()}, {$length}), {$this->length}))", $length);
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
