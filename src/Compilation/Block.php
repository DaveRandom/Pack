<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

abstract class Block implements Compilable
{
    /**
     * @var Compilable[]
     */
    protected $codeElements = [];

    /**
     * @param Compilable[] ...$elements
     * @return $this
     */
    final public function appendCodeElements(Compilable ...$elements): self
    {
        foreach ($elements as $element) {
            $this->codeElements[] = $element;
        }

        return $this;
    }
}
