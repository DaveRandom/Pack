<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use DaveRandom\Pack\Compilation\Unpack\CompilationContext as UnpackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class NullTerminatedString implements Type
{
    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        static $packSpecifier = 'Z*';
        static $literalFormat = '"{%s}\x00"';

        if ($count === null) {
            if ($ctx->hasPendingPackSpecifiers()) {
                $ctx->appendPackSpecifier($packSpecifier);
            } else {
                $ctx->appendResult(\sprintf($literalFormat, $ctx->getCurrentArg()));
            }

            return;
        }

        if ($count === UNBOUNDED) {
            $ctx->beginIterateCurrentArg();
            $ctx->appendResult(\sprintf($literalFormat, $ctx->getCurrentArg()));
            $ctx->endIterateCurrentArg();
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $arg = "{$ctx->getCurrentArg()}[{$i}]";

            if ($ctx->hasPendingPackSpecifiers()) {
                $ctx->appendPackSpecifier($packSpecifier, null, $arg);
            } else {
                $ctx->appendResult(\sprintf($literalFormat, $arg));
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
