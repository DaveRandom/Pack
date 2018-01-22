<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

use DaveRandom\Pack\Compilation\Compilable;

final class AssignmentOperation implements Compilable
{
    private $varName;
    private $expressions;

    public function __construct(string $varName, array $expressions)
    {
        $this->varName = $varName;
        $this->expressions = $expressions;
    }

    public function compile(int $indentation, int $increment, string $target = ''): string
    {
        $padding = \str_repeat(' ', $indentation);
        $continuationPadding = $padding . \str_repeat(' ', $increment);

        return "{$padding}{$target} " . \implode("\n{$continuationPadding}. ", $this->expressions) . ";\n";
    }

    public function compileAsAssignment(int $indentation, int $increment, string $operator): string
    {
        return $this->compile($indentation, $increment, "{$this->varName} {$operator}");
    }

    public function compileAsReturn(int $indentation, int $increment, bool $withResult): string
    {
        return $this->compile($indentation, $increment, $withResult ? "return {$this->varName} ." : 'return');
    }
}
