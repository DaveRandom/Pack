<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

abstract class Block implements CodeElement
{
    /**
     * @var CodeElement[]
     */
    protected $codeElements = [];

    /**
     * @param CodeElement[] ...$elements
     * @return $this
     */
    final public function appendCodeElements(CodeElement ...$elements): self
    {
        foreach ($elements as $element) {
            $this->codeElements[] = $element;
        }

        return $this;
    }
}
