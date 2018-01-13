<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class PackCompilationContext
{
    private $argIndex = 0;
    private $consumeToEnd = false;
    private $specifiers = [];
    private $specifierArgCount = 0;
    private $codeLines = [];
    private $haveResult = false;

    private function processEndOfSpecifiers()
    {
        $specifiers = \var_export(\implode('', $this->specifiers), true);

        $packArgIndexes = [];
        for ($i = 0; $i < $this->specifierArgCount; $i++) {
            $packArgIndexes[] = $this->argIndex++;
        }
        $args = '$args[' . \implode('], $args[', $packArgIndexes) . ']';

        $this->specifiers = [];
        $this->specifierArgCount = 0;

        $this->codeLines[] = "\$result {$this->getAssignmentOperator()} \pack({$specifiers}, {$args});";
    }

    public function appendSpecifier(string $specifier, int $argCount)
    {
        $this->specifiers[] = $specifier . ([1 => '', Element::REPEAT => '*'][$argCount] ?? $argCount);

        if ($argCount === Element::REPEAT) {
            $this->consumeToEnd = true;
        } else {
            $this->specifierArgCount += $argCount;
        }
    }

    public function appendCode($lines)
    {
        \array_push($this->codeLines, ...((array)$lines));
    }

    public function consumeArg(): int
    {
        return $this->argIndex++;
    }

    public function getAssignmentOperator(): string
    {
        if (!empty($this->specifiers)) {
            $this->processEndOfSpecifiers();
        }

        if ($this->haveResult) {
            return '.=';
        }

        $this->haveResult = true;
        return '=';
    }

    public function getCodeLines(): array
    {
        if (!empty($this->specifiers)) {
            $this->processEndOfSpecifiers();
        }

        return $this->codeLines;
    }
}
