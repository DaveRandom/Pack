<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;
use const DaveRandom\Pack\UNBOUNDED;

final class LengthPrefixedArrayOf implements VectorType
{
    private static $iterationDepth = 0;

    private $elementType;
    private $lengthType;

    public function __construct(Type $elementType, IntegerType $lengthType)
    {
        if ($elementType instanceof VectorType && !$elementType->isFinite()) {
            throw new \InvalidArgumentException('Unbounded array must be the last element of the top level structure');
        }

        $this->elementType = $elementType;
        $this->lengthType = $lengthType;
    }

    public function generatePackCode(PackMethod $method, int $count = null)
    {
        $this->lengthType->generatePackCodeForExpression($method, "\count({$method->getCurrentArg()})");

        $method->beginIterateCurrentArg();
        $this->elementType->generatePackCode($method);
        $method->endIterateCurrentArg();
    }

    public function generateUnpackCode(UnpackMethod $method, int $count = null)
    {
        $counterVarName = '$lpa' . self::$iterationDepth++;

        $this->lengthType->generateUnpackCodeForSingleValueAtCurrentOffset($method, $counterVarName);

        $method->beginIterateCounter($counterVarName, true);
        $this->elementType->generateUnpackCode($method);
        $method->endIterateCounter();

        self::$iterationDepth--;
    }

    public function isFixedSize(): bool
    {
        return false;
    }

    public function getSize(): int
    {
        return UNBOUNDED;
    }

    public function isFinite(): bool
    {
        return true;
    }

    public function count(): int
    {
        return UNBOUNDED;
    }
}
