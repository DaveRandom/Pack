<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

final class Statement implements CodeElement
{
    private $statement;

    public function __construct(string $statement)
    {
        $this->statement = $statement;
    }

    public function getCode(int $indentation, int $increment, string $assignmentOperator): string
    {
        return \str_repeat(' ', $indentation) . $this->statement;
    }
}
