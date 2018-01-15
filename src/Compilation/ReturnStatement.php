<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

final class ReturnStatement implements CodeElement
{
    public function getCode(int $indentation, int $increment, string $assignmentOperator): string
    {
        return \str_repeat(' ', $indentation) . 'return ' . PackCompilationContext::RESULT_VAR_NAME . ';';
    }
}
