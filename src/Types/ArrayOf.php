<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\PackCompilationContext;
use const DaveRandom\Pack\UNBOUNDED;

final class ArrayOf implements VectorType
{
    private $element;
    private $bounds;

    private function generatePackCodeForCurrentArg(PackCompilationContext $ctx)
    {
        $this->element->generatePackCode($ctx, $this->bounds);
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
            $this->element->generatePackCode($ctx, $this->bounds);
            $ctx->popArgDimension();
        }
    }

    public function __construct(Type $element, int $bounds = UNBOUNDED)
    {
        if ($bounds < 1 && $bounds !== UNBOUNDED) {
            throw new \InvalidArgumentException('Bounds of array must be positive integer or unbounded');
        }

        if ($element instanceof VectorType && !$element->isFinite() && $bounds !== 1) {
            throw new \InvalidArgumentException('Unbounded array must be the last element of the top level structure');
        }

        $this->element = $element;
        $this->bounds = $bounds;
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($count === null) {
            $this->generatePackCodeForCurrentArg($ctx);
        } else if ($count === UNBOUNDED) {
            $this->generatePackCodeForUnboundedArray($ctx);
        } else {
            $this->generatePackCodeForBoundedArray($ctx, $count);
        }
    }

    public function count(): int
    {
        return $this->bounds;
    }

    public function isFinite(): bool
    {
        return $this->bounds !== UNBOUNDED;
    }
}
