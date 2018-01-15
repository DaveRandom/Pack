<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

final class Block implements CodeElement
{
    private $header;
    private $trailer;

    /** @var CodeElement[] */
    private $innerCodeElements = [];

    public function __construct(string $header = null, string $trailer = null)
    {
        $this->header = $header;
        $this->trailer = $trailer;
    }

    public function appendElement(CodeElement $element)
    {
        $this->innerCodeElements[] = $element;
    }

    public function isRoot()
    {
        return $this->header === null && $this->trailer === null;
    }

    public function getCode(int $indentation, int $increment, string $assignmentOperator): string
    {
        if ($this->isRoot() && \count($this->innerCodeElements) === 1 && $this->innerCodeElements[0] instanceof AssignmentOperation) {
            return \str_repeat(' ', $indentation) . "return {$this->innerCodeElements[0]->getCode($indentation, $increment, '=')};\n";
        }

        if ($this->isRoot()) {
            if (!$this->innerCodeElements[0] instanceof AssignmentOperation) {
                \array_unshift($this->innerCodeElements, new AssignmentOperation(["''"]));
            }

            \array_push($this->innerCodeElements, new ReturnStatement());
        }

        $result = '';
        $padding = \str_repeat(' ', $indentation);
        $innerIndentation = $indentation;

        if (!$this->isRoot()) {
            $result .= $padding . \ltrim("{$this->header} {\n");
            $innerIndentation += $increment;
        }

        foreach ($this->innerCodeElements as $element) {
            if ($element instanceof AssignmentOperation) {
                $result .= $padding . \str_repeat(' ', $increment) . PackCompilationContext::RESULT_VAR_NAME
                    . " {$assignmentOperator} {$element->getCode($innerIndentation, $increment, $assignmentOperator)};\n";
                $assignmentOperator = '.=';
            } else {
                $result .= \rtrim($element->getCode($innerIndentation, $increment, $assignmentOperator)) . "\n";
            }
        }

        if (!$this->isRoot()) {
            $result .= \rtrim("{$padding}} {$this->trailer}") . "\n";
        }

        return $result;
    }
}
