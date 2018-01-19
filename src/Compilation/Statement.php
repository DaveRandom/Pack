<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

final class Statement implements CodeElement
{
    private $statement;

    public function __construct(string $statement, ...$args)
    {
        $this->statement = !empty($args)
            ? \vsprintf(\trim($statement), $args)
            : \trim($statement);
    }

    public function getCode(int $indentation, int $increment): string
    {
        return \str_repeat(' ', $indentation) . \rtrim($this->statement, ';') . ";\n";
    }
}
