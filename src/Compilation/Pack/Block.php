<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

abstract class Block implements CodeElement
{
    protected function generateAssignmentOperationCode(CodeElement $op, string $operator, int $indentation, int $increment): string
    {
        return \str_repeat(' ', $indentation)
            . CompilationContext::RESULT_VAR_NAME
            . " {$operator} {$op->getCode($indentation, $increment)};\n";
    }

    abstract public function appendElement(CodeElement $element);
}
