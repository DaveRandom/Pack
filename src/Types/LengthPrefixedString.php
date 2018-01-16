<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class LengthPrefixedString implements VectorType
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

            if ($ctx->hasPendingPackSpecifiers()) {
                $ctx->appendPackSpecifier('a*');
            } else {
                $ctx->appendResult($ctx->getCurrentArg());
            }

            return;
        }

        if ($count === UNBOUNDED) {
            $ctx->beginIterateCurrentArg();
            $this->lengthType->generatePackCodeForExpression($ctx, "\strlen({$ctx->getCurrentArg()})");

            if ($ctx->hasPendingPackSpecifiers()) {
                $ctx->appendPackSpecifier('a*');
            } else {
                $ctx->appendResult($ctx->getCurrentArg());
            }

            $ctx->endIterateCurrentArg();
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $arg = "{$ctx->getCurrentArg()}[{$i}]";
            $this->lengthType->generatePackCodeForExpression($ctx, "\strlen({$arg})");

            if ($ctx->hasPendingPackSpecifiers()) {
                $ctx->appendPackSpecifier('a*', null, $arg);
            } else {
                $ctx->appendResult($arg);
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
