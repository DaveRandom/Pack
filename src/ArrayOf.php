<?php declare(strict_types=1);

namespace DaveRandom\Pack;


final class ArrayOf implements Vector
{
    private $element;
    private $bounds;

    public function __construct(Type $element, int $bounds = UNBOUNDED)
    {
        if ($bounds < 1 && $bounds !== UNBOUNDED) {
            throw new \InvalidArgumentException('Bounds of array must be positive integer or unbounded');
        }

        if ($element instanceof Vector && !$element->isFinite() && $bounds !== 1) {
            throw new \InvalidArgumentException('Unbounded array must be the last element of the top level structure');
        }

        $this->element = $element;
        $this->bounds = $bounds;
    }

    private function generateUnboundedArrayPackCode(PackCompilationContext $ctx)
    {
        $ctx->appendResult();

        $ctx->beginIterateCurrentArg();
        $this->element->generatePackCode($ctx, $this->bounds);
        $ctx->endIterateCurrentArg();
    }

    private function generateArrayPackCode(PackCompilationContext $ctx, int $count)
    {
        if ($count === UNBOUNDED) {
            $this->generateUnboundedArrayPackCode($ctx);
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $ctx->pushArgDimension($i);

            $this->element->generatePackCode($ctx, $this->bounds);

            $ctx->popArgDimension();
        }
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($count !== null) {
            $this->generateArrayPackCode($ctx, $count);
            return;
        }

        $this->element->generatePackCode($ctx, $this->bounds);
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
