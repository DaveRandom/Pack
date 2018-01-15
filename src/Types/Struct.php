<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\PackCompilationContext;
use function DaveRandom\Pack\is_valid_name;
use const DaveRandom\Pack\UNBOUNDED;

final class Struct implements VectorType
{
    private $elements = [];
    private $finite;

    private function generatePackCodeForCurrentArg(PackCompilationContext $ctx)
    {
        foreach ($this->elements as $key => $element) {
            $ctx->pushArgDimension($key);
            $element->generatePackCode($ctx);
            $ctx->popArgDimension();
        }
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
            $this->generatePackCodeForCurrentArg($ctx);
            $ctx->popArgDimension();
        }
    }

    /**
     * @param Type[] $elements
     */
    public function __construct(array $elements)
    {
        $finite = true;

        foreach ($elements as $name => $element) {
            if (!is_valid_name($name)) {
                throw new \InvalidArgumentException("Invalid struct element name: {$name}");
            }

            if (!$element instanceof Type) {
                throw new \InvalidArgumentException(Struct::class . ' may only contain instances of ' . Type::class);
            }

            if (!$element instanceof VectorType || $element->isFinite()) {
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
        return \count($this->elements);
    }

    public function isFinite(): bool
    {
        return $this->finite;
    }
}
