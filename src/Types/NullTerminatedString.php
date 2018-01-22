<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;
use const DaveRandom\Pack\UNBOUNDED;

final class NullTerminatedString implements Type
{
    public function generatePackCode(PackMethod $method, int $count = null)
    {
        static $packSpecifier = 'Z*';
        static $literalFormat = '"{%s}\x00"';

        if ($count === null) {
            if ($method->hasPendingPackSpecifiers()) {
                $method->appendPackSpecifier($packSpecifier);
            } else {
                $method->appendResult(\sprintf($literalFormat, $method->getCurrentArg()));
            }

            return;
        }

        if ($count === UNBOUNDED) {
            $method->beginIterateCurrentArg();
            $method->appendResult(\sprintf($literalFormat, $method->getCurrentArg()));
            $method->endIterateCurrentArg();
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $arg = "{$method->getCurrentArg()}[{$i}]";

            if ($method->hasPendingPackSpecifiers()) {
                $method->appendPackSpecifier($packSpecifier, null, $arg);
            } else {
                $method->appendResult(\sprintf($literalFormat, $arg));
            }
        }
    }

    public function generateUnpackCode(UnpackMethod $method, int $count = null)
    {
        if ($count === null) {
            $method->appendResultWithSizeExpr("\unpack('Z*', {$method->getData()}, {$method->getOffset()})[1]", "\strlen({$method->getCurrentTarget()}) + 1");
            return;
        }

        if ($count === UNBOUNDED) {
            $method->beginConsumeRemainingData();
            $method->appendResultWithSizeExpr("\unpack('Z*', {$method->getData()}, {$method->getOffset()})[1]", "\strlen({$method->getCurrentTarget()}) + 1");
            $method->endConsumeRemainingData();
            return;
        }

        $method->beginIterateCounter($count, true);
        $method->appendResultWithSizeExpr("\unpack('Z*', {$method->getData()}, {$method->getOffset()})[1]", "\strlen({$method->getCurrentTarget()}) + 1");
        $method->endIterateCounter();
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
