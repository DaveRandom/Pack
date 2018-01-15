<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

final class AssignmentOperation implements CodeElement
{
    private $expressions;

    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    public function getCode(int $indentation, int $increment, string $assignmentOperator): string
    {
        $result = $this->expressions[0];
        $padding = \str_repeat(' ', $indentation + $increment);

        for ($i = 1; isset($this->expressions[$i]); $i++) {
            $result .= "\n{$padding}. {$this->expressions[$i]}";
        }

        return $result;
    }
}
