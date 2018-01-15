<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\PackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class LengthPrefixedString implements Vector
{
    private $lengthType;

    public function __construct(IntegerType $lengthType)
    {
        $this->lengthType = $lengthType;
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($count === null) {
            $this->lengthType->generatePackCodeForExpression($ctx, "\strlen({$ctx->getCurrentArg()})");
            $ctx->appendResult($ctx->getCurrentArg());
        } else if ($count === UNBOUNDED) {
            $ctx->beginIterateCurrentArg();
            $this->lengthType->generatePackCodeForExpression($ctx, "\strlen({$ctx->getCurrentArg()})");
            $ctx->appendResult($ctx->getCurrentArg());
            $ctx->endIterateCurrentArg();
        } else {
            for ($i = 0; $i < $count; $i++) {
                $this->lengthType->generatePackCodeForExpression($ctx, "{$ctx->getCurrentArg()}[{$i}]");
                $ctx->appendResult("{$ctx->getCurrentArg()}[{$i}]");
            }
        }
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
