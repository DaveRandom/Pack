<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

interface CodeElement
{
    public function getCode(int $indentation, int $increment, string $assignmentOperator): string;
}
