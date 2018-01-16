<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class ArrayOf implements VectorType
{
    private $elementType;
    private $bounds;
    private $finite;
    private $size;

    private function generatePackCodeForCurrentArg(PackCompilationContext $ctx)
    {
        $this->elementType->generatePackCode($ctx, $this->bounds);
    }

    private function generatePackCodeForUnboundedArray(PackCompilationContext $ctx)
    {
        $ctx->beginIterateCurrentArg();
        $this->generatePackCodeForCurrentArg($ctx);
        $ctx->endIterateCurrentArg();
    }

    private function generatePackCodeForBoundedArray(PackCompilationContext $ctx, int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $ctx->pushArgDimension($i);
            $this->elementType->generatePackCode($ctx, $this->bounds);
            $ctx->popArgDimension();
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

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($count === null) {
            $this->generatePackCodeForCurrentArg($ctx);
            return;
        }

        if ($count === UNBOUNDED) {
            $this->generatePackCodeForUnboundedArray($ctx);
            return;
        }

        $this->generatePackCodeForBoundedArray($ctx, $count);
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
