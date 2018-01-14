<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

final class Statement
{
    private $statement;

    public function __construct(string $statement)
    {
        $this->statement = $statement;
    }

    public function getCode(int $indentation): string
    {
        return \str_repeat(' ', $indentation) . $this->statement;
    }
}
