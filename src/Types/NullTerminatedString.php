<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use DaveRandom\Pack\Compilation\Unpack\CompilationContext as UnpackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class NullTerminatedString implements Type
{
    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($count === null) {
            if ($ctx->hasPendingPackSpecifiers()) {
                $ctx->appendPackSpecifier('a*x');
            } else {
                $ctx->appendResult('"{' . $ctx->getCurrentArg() . '}\x00"');
            }

            return;
        }

        if ($count === UNBOUNDED) {
            $ctx->beginIterateCurrentArg();
            $ctx->appendResult('"{' . $ctx->getCurrentArg() . '}\x00"');
            $ctx->endIterateCurrentArg();
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $arg = $ctx->getCurrentArg() . '[' . $i . ']';

            if ($ctx->hasPendingPackSpecifiers()) {
                $ctx->appendPackSpecifier('a*x', null, $arg);
            } else {
                $ctx->appendResult('"{' . $arg . '}\x00"');
            }
        }
    }

    public function generateUnpackCode(UnpackCompilationContext $context, int $count = null)
    {
        // todo
        throw new \Error("Not implemented yet");
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
