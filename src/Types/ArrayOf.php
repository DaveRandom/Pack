<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;
use const DaveRandom\Pack\UNBOUNDED;

final class ArrayOf implements VectorType
{
    private $elementType;
    private $bounds;
    private $finite;
    private $size;

    private function generatePackCodeForCurrentArg(PackMethod $method)
    {
        $this->elementType->generatePackCode($method, $this->bounds);
    }

    private function generatePackCodeForUnboundedArray(PackMethod $method)
    {
        $method->beginIterateCurrentArg();
        $this->generatePackCodeForCurrentArg($method);
        $method->endIterateCurrentArg();
    }

    private function generatePackCodeForBoundedArray(PackMethod $method, int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $method->pushArgDimension($i);
            $this->elementType->generatePackCode($method, $this->bounds);
            $method->popArgDimension();
        }
    }

    private function generateUnpackCodeForCurrentTarget(UnpackMethod $method)
    {
        $this->elementType->generateUnpackCode($method, $this->bounds);
    }

    private function generateUnpackCodeForUnboundedArray(UnpackMethod $method)
    {
        $method->beginConsumeRemainingData();
        $this->generateUnpackCodeForCurrentTarget($method);
        $method->endConsumeRemainingData();
    }

    private function generateUnpackCodeForBoundedArray(UnpackMethod $method, int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $method->pushTargetDimension($i);
            $this->elementType->generateUnpackCode($method, $this->bounds);
            $method->popTargetDimension();
        }
    }

    public function __construct(Type $elementType, int $bounds = UNBOUNDED)
    {
        if ($bounds < 1 && $bounds !== UNBOUNDED) {
            throw new \InvalidArgumentException('Bounds of array must be positive integer or unbounded');
        }

        if ($elementType instanceof VectorType && !$elementType->isFinite() && $bounds !== 1) {
            throw new \InvalidArgumentException('Unbounded array must be the last element of the top level structure');
        }

        $this->elementType = $elementType;
        $this->bounds = $bounds;
        $this->finite = $bounds !== UNBOUNDED;
        $this->size = $bounds !== UNBOUNDED && $this->elementType->isFixedSize()
            ? $bounds * $this->elementType->getSize()
            : UNBOUNDED;
    }

    public function generatePackCode(PackMethod $method, int $count = null)
    {
        if ($count === null) {
            $this->generatePackCodeForCurrentArg($method);
            return;
        }

        if ($count === UNBOUNDED) {
            $this->generatePackCodeForUnboundedArray($method);
            return;
        }

        $this->generatePackCodeForBoundedArray($method, $count);
    }

    public function generateUnpackCode(UnpackMethod $method, int $count = null)
    {
        if ($count === null) {
            $this->generateUnpackCodeForCurrentTarget($method);
            return;
        }

        if ($count === UNBOUNDED) {
            $this->generateUnpackCodeForUnboundedArray($method);
            return;
        }

        $this->generateUnpackCodeForBoundedArray($method, $count);
    }

    public function isFixedSize(): bool
    {
        return $this->size !== UNBOUNDED;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function count(): int
    {
        return $this->bounds;
    }

    public function isFinite(): bool
    {
        return $this->finite;
    }
}
