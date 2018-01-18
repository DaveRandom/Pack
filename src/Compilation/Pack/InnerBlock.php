<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

final class InnerBlock extends Block
{
    private $header;
    private $trailer;

    /** @var CodeElement[] */
    private $innerCodeElements = [];

    public function __construct(string $header, string $trailer = null)
    {
        $this->header = $header;
        $this->trailer = $trailer;
    }

    public function appendElement(CodeElement $element)
    {
        $this->innerCodeElements[] = $element;
    }

    public function getCode(int $indentation, int $increment): string
    {
        $padding = \str_repeat(' ', $indentation);
        $indentation += $increment;

        $result = $padding . \ltrim("{$this->header} {\n");

        foreach ($this->innerCodeElements as $element) {
            $result .= $element instanceof AssignmentOperation
                ? $this->generateAssignmentOperationCode($element, '.=', $indentation, $increment)
                : \rtrim($element->getCode($indentation, $increment)) . "\n";
        }

        return $result . \rtrim("{$padding}} {$this->trailer}") . "\n";
    }
}
