<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

final class RootBlock extends Block
{
    /** @var CodeElement[] */
    private $elements = [];

    public function appendElement(CodeElement $element)
    {
        $this->elements[] = $element;
    }

    public function getCode(int $indentation, int $increment): string
    {
        if (\count($this->elements) === 1 && $this->elements[0] instanceof AssignmentOperation) {
            return \str_repeat(' ', $indentation) . "return {$this->elements[0]->getCode($indentation, $increment)};\n";
        }

        $result = '';
        $padding = \str_repeat(' ', $indentation);
        $operator = '=';

        if (!$this->elements[0] instanceof AssignmentOperation) {
            $result .= $padding . CompilationContext::RESULT_VAR_NAME . " = '';";
            $operator = '.=';
        }

        for ($i = 0, $l = \count($this->elements) - 1; $i < $l; $i++) {
            if (!$this->elements[$i] instanceof AssignmentOperation) {
                $result .= \rtrim($this->elements[$i]->getCode($indentation, $increment)) . "\n";
                continue;
            }

            $result .= $this->generateAssignmentOperationCode($this->elements[$i], $operator, $indentation, $increment);
            $operator = '.=';
        }

        if ($this->elements[$i] instanceof AssignmentOperation) {
            return $result . $padding . 'return ' . CompilationContext::RESULT_VAR_NAME . ' . '
                . $this->elements[$i]->getCode($indentation, $increment);
        }

        return $result . \rtrim($this->elements[$i]->getCode($indentation, $increment)) . "\n"
                . $padding . 'return ' . CompilationContext::RESULT_VAR_NAME . ';';
    }
}
