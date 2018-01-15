<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class NullTerminatedString
{
    private $lengthType;

    public function __construct(IntegerType $lengthType)
    {
        $this->lengthType = $lengthType;
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($count === null) {
            $ctx->appendResult($ctx->getCurrentArg() . ' . "\x00"');
        } else if ($count === UNBOUNDED) {
            $ctx->beginIterateCurrentArg();
            $ctx->appendResult($ctx->getCurrentArg() . ' . "\x00"');
            $ctx->endIterateCurrentArg();
        } else {
            for ($i = 0; $i < $count; $i++) {
                $ctx->appendResult($ctx->getCurrentArg() . ' . "\x00"');
            }
        }
    }

    public function isFixedSize(): bool
    {
        return false;
    }

    public function getSize(): int
    {
        return UNBOUNDED;
    }

    public function count(): int
    {
        return UNBOUNDED;
    }

    public function isFinite(): bool
    {
        return true;
    }
}
