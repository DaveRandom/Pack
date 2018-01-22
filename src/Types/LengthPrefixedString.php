<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;
use const DaveRandom\Pack\UNBOUNDED;

final class LengthPrefixedString implements VectorType
{
    private $lengthType;

    public function __construct(IntegerType $lengthType)
    {
        $this->lengthType = $lengthType;
    }

    public function generatePackCode(PackMethod $method, int $count = null)
    {
        if ($count === null) {
            $this->lengthType->generatePackCodeForExpression($method, "\strlen({$method->getCurrentArg()})");

            if ($method->hasPendingPackSpecifiers()) {
                $method->appendPackSpecifier('a*');
            } else {
                $method->appendResult($method->getCurrentArg());
            }

            return;
        }

        if ($count === UNBOUNDED) {
            $method->beginIterateCurrentArg();
            $this->lengthType->generatePackCodeForExpression($method, "\strlen({$method->getCurrentArg()})");

            if ($method->hasPendingPackSpecifiers()) {
                $method->appendPackSpecifier('a*');
            } else {
                $method->appendResult($method->getCurrentArg());
            }

            $method->endIterateCurrentArg();
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $arg = "{$method->getCurrentArg()}[{$i}]";
            $this->lengthType->generatePackCodeForExpression($method, "\strlen({$arg})");

            if ($method->hasPendingPackSpecifiers()) {
                $method->appendPackSpecifier('a*', null, $arg);
            } else {
                $method->appendResult($arg);
            }
        }
    }

    public function generateUnpackCode(UnpackMethod $method, int $count = null)
    {
        if ($count === null) {
            $this->lengthType->generateUnpackCodeForSingleValueAtCurrentOffset($method, '$length');
            $method->appendLengthCheck('$length');
            $method->appendResultWithSizeExpr("\unpack('a' . \$length, {$method->getData()}, {$method->getOffset()})[1]", '$length');

            return;
        }

        if ($count === UNBOUNDED) {
            $method->beginConsumeRemainingData();

            $this->lengthType->generateUnpackCodeForSingleValueAtCurrentOffset($method, '$length');
            $method->appendLengthCheck('$length');
            $method->appendResultWithSizeExpr("\unpack('a' . \$length, {$method->getData()}, {$method->getOffset()})[1]", '$length');

            $method->endConsumeRemainingData();

            return;
        }

        $method->beginIterateCounter($count, true);

        $this->lengthType->generateUnpackCodeForSingleValueAtCurrentOffset($method, '$length');
        $method->appendLengthCheck('$length');
        $method->appendResultWithSizeExpr("\unpack('a' . \$length, {$method->getData()}, {$method->getOffset()})[1]", '$length');

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
