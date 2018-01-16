<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

use const DaveRandom\Pack\UNBOUNDED;

final class CompilationContext
{
    const RESULT_VAR_NAME = '$‽r';

    private $dataVarName;
    private $offsetVarName;
    private $countVarName;

    private $code = [];

    private $currentTargetPath = [];

    private $pendingUnpackSpecifiers;

    private function compilePendingUnpackSpecifiers()
    {
        $specifiers = [];
        $targets = [];
        $totalSize = 0;

        for ($i = 0; $this->pendingUnpackSpecifiers->count() > 0; $i++) {
            list($specifier, $size, $count, $target) = $this->pendingUnpackSpecifiers->dequeue();

            if ($count === null) { // scalar
                $specifiers[] = "{$specifier}i{$i}";
                $totalSize += $size;
                $targets[] = $target;
                continue;
            }

            if ($count === UNBOUNDED) { // unbounded array
                // todo
                continue;
            }

            // bounded array
            $totalSize += $size * $count;

            for ($j = 0; $j < $count; $i++, $j++) {
                $specifiers[] = "{$specifier}i{$i}";
                $targets[] = "{$target}[{$j}]";
            }
        }

        $targetList = \implode(', ', $targets);
        $specifierString = \var_export(\implode('/', $specifiers), true);

        $this->appendCode(
            "list({$targetList}) = \array_values(\unpack({$specifierString}, {$this->dataVarName}, {$this->offsetVarName}));",
            "{$this->offsetVarName} += {$totalSize};",
            "{$this->countVarName} += {$totalSize};"
        );
    }

    public function __construct(string $dataVarName, string $offsetVarName, string $countVarName)
    {
        $this->dataVarName = $dataVarName;
        $this->offsetVarName = $offsetVarName;
        $this->countVarName = $countVarName;

        $this->pendingUnpackSpecifiers = new \SplQueue();
    }

    public function hasPendingUnpackSpecifiers(): bool
    {
        return $this->pendingUnpackSpecifiers->count() > 0;
    }

    public function getCurrentTarget(): string
    {
        return $this->getTarget($this->currentTargetPath);
    }

    public function getTarget(array $path): string
    {
        return !empty($path)
            ? self::RESULT_VAR_NAME . '[' . \implode('][', $path) . ']'
            : self::RESULT_VAR_NAME;
    }

    public function getData(): string
    {
        return $this->dataVarName;
    }

    public function getOffset(): string
    {
        return $this->offsetVarName;
    }

    public function appendUnpackSpecifier(string $specifier, int $size, int $count = null, string $target = null)
    {
        $this->pendingUnpackSpecifiers->push([$specifier, $size, $count, $target ?? $this->getCurrentTarget()]);
    }

    public function appendResult(string $expr, int $size)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $this->appendCode(
            "{$this->getCurrentTarget()} = {$expr};",
            "{$this->offsetVarName} += {$size};",
            "{$this->countVarName} += {$size};"
        );
    }

    public function appendCode(string ...$statements)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        foreach ($statements as $statement) {
            $this->code[] = $statement;
        }
    }

    public function beginConsumeRemainingData()
    {
        // todo
    }

    public function endConsumeRemainingData()
    {
        // todo
    }

    public function pushTargetDimension($key)
    {
        $this->currentTargetPath[] = \var_export($key, true);
    }

    public function popTargetDimension()
    {
        return \array_pop($this->currentTargetPath);
    }

    public function getCode(int $indentation, int $increment): string
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $padding = \str_repeat(' ', $indentation);
        return $padding . self::RESULT_VAR_NAME . " = [];\n"
            . $padding . implode("\n{$padding}", $this->code) . "\n{$padding}return " . self::RESULT_VAR_NAME . ";\n";
    }
}
