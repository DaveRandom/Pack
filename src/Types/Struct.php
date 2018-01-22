<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;
use function DaveRandom\Pack\is_valid_name;
use const DaveRandom\Pack\UNBOUNDED;

final class Struct implements VectorType
{
    /** @var Type[] */
    private $elements = [];
    private $finite;
    private $size;

    private function generatePackCodeForCurrentArg(PackMethod $method)
    {
        foreach ($this->elements as $key => $element) {
            $method->pushArgDimension($key);
            $element->generatePackCode($method);
            $method->popArgDimension();
        }
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
            $this->generatePackCodeForCurrentArg($method);
            $method->popArgDimension();
        }
    }

    private function generateUnpackCodeForCurrentTarget(UnpackMethod $method)
    {
        foreach ($this->elements as $key => $element) {
            $method->pushTargetDimension($key);
            $element->generateUnpackCode($method);
            $method->popTargetDimension();
        }
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
            $this->generateUnpackCodeForCurrentTarget($method);
            $method->popTargetDimension();
        }
    }

    /**
     * @param Type[] $elements
     */
    public function __construct(array $elements)
    {
        $finite = true;
        $size = 0;

        foreach ($elements as $name => $element) {
            if (!is_valid_name($name)) {
                throw new \InvalidArgumentException("Invalid struct element name: {$name}");
            }

            if (!$element instanceof Type) {
                throw new \InvalidArgumentException(Struct::class . ' may only contain instances of ' . Type::class);
            }

            if ($size !== UNBOUNDED && $element->isFixedSize()) {
                $size += $element->getSize();
            } else {
                $size = UNBOUNDED;
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
        $this->size = $size;
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
        return \count($this->elements);
    }

    public function isFinite(): bool
    {
        return $this->finite;
    }
}
