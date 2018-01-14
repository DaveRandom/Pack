<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class Struct implements Vector
{
    /** @var Element[] */
    private $elements = [];
    private $finite;

    /**
     * @param Element[] $elements
     */
    public function __construct(array $elements)
    {
        $finite = true;

        foreach ($elements as $name => $element) {
            if (!\preg_match('/[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*/i', $name)) {
                throw new \InvalidArgumentException("Invalid struct element name: {$name}");
            }

            if (!$element instanceof Element) {
                throw new \InvalidArgumentException(Struct::class . ' may only contain instances of ' . Element::class);
            }

            if (!$element instanceof Vector || $element->isFinite()) {
                continue;
            }

            if (!$finite) {
                throw new \InvalidArgumentException('Unbounded array must be the last element of the top level structure');
            }

            $finite = false;
        }

        $this->elements = $elements;
        $this->finite = $finite;
    }

    private function generateUnboundedArrayPackCode(PackCompilationContext $ctx)
    {
        $ctx->appendResult();

        $ctx->beginIterateCurrentArg();

        foreach ($this->elements as $key => $element) {
            $ctx->pushArgDimension($key);
            $element->generatePackCode($ctx);
            $ctx->popArgDimension();
        }

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

            foreach ($this->elements as $key => $element) {
                $ctx->pushArgDimension($key);

                $element->generatePackCode($ctx);

                $ctx->popArgDimension();
            }

            $ctx->popArgDimension();
        }
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($count !== null) {
            $this->generateArrayPackCode($ctx, $count);
            return;
        }

        foreach ($this->elements as $key => $element) {
            $ctx->pushArgDimension($key);

            $element->generatePackCode($ctx);

            $ctx->popArgDimension();
        }
    }

    public function count(): int
    {
        return \count($this->elements);
    }

    public function isFinite(): bool
    {
        return $this->finite;
    }
}
