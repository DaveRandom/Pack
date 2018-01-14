<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

final class Block
{
    private $header;
    private $trailer;
    private $innerCodeElements = [];

    public function __construct(string $header = null, string $trailer = null)
    {
        $this->header = $header;
        $this->trailer = $trailer;
    }

    public function appendStatement(Statement $statement)
    {
        $this->innerCodeElements[] = $statement;
    }

    public function appendBlock(Block $block)
    {
        $this->innerCodeElements[] = $block;
    }

    public function isRoot()
    {
        return $this->header === null && $this->trailer === null;
    }

    public function getCode(int $indentation, int $increment): string
    {
        $result = '';
        $padding = \str_repeat(' ', $indentation);
        $innerIndentation = $indentation;

        if (!$this->isRoot()) {
            $result .= $padding . \ltrim("{$this->header} {\n");
            $innerIndentation += $increment;
        }

        foreach ($this->innerCodeElements as $element) {
            if ($element instanceof Statement) {
                $result .= $element->getCode($innerIndentation) . "\n";
            } else if ($element instanceof Block) {
                $result .= $element->getCode($innerIndentation, $increment) . "\n";
            }
        }

        if (!$this->isRoot()) {
            $result .= \rtrim("{$padding}} {$this->trailer}") . "\n";
        }

        return $result;
    }
}
