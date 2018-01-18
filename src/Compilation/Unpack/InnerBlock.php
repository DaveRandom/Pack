<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

final class InnerBlock implements Block
{
    private $header;
    private $trailer;

    /** @var CodeElement[] */
    private $codeElements = [];

    public function __construct(string $header, string $trailer = null)
    {
        $this->header = $header;
        $this->trailer = $trailer;
    }

    public function appendCodeElements(CodeElement ...$elements): self
    {
        foreach ($elements as $element) {
            $this->codeElements[] = $element;
        }

        return $this;
    }

    public function getCode(int $indentation, int $increment): string
    {
        $padding = \str_repeat(' ', $indentation);
        $innerIndentation = $indentation + $increment;

        $result = "{$padding}{$this->header} {\n";

        foreach ($this->codeElements as $element) {
            $result .= $element->getCode($innerIndentation, $increment);
        }

        return $result . "{$padding}}" . \ltrim(' ' . $this->trailer) . "\n";
    }
}
