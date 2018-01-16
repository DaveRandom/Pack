<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class NullPaddedString implements VectorType
{
    private $length;

    public function __construct(int $length)
    {
        $this->length = $length;
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        $arg = $ctx->getCurrentArg();
        $specifier = "a{$this->length}";

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
