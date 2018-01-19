<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

use DaveRandom\Pack\Compilation\CodeElement;

final class AssignmentOperation implements CodeElement
{
    private $varName;
    private $expressions;

    public function __construct(string $varName, array $expressions)
    {
        $this->varName = $varName;
        $this->expressions = $expressions;
    }

    public function getCode(int $indentation, int $increment, string $target = ''): string
    {
        $padding = \str_repeat(' ', $indentation);
        $continuationPadding = $padding . \str_repeat(' ', $increment);

        return "{$padding}{$target} " . \implode("\n{$continuationPadding}. ", $this->expressions) . ";\n";
    }

    public function getCodeAsAssignment(int $indentation, int $increment, string $operator): string
    {
        return $this->getCode($indentation, $increment, "{$this->varName} {$operator}");
    }

    public function getCodeAsReturn(int $indentation, int $increment, bool $withResult): string
    {
        return $this->getCode($indentation, $increment, $withResult ? "return {$this->varName} ." : 'return');
    }
}
