<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class PackCompilationContext
{
    const ARGS_VAR_NAME = '‽args‽';
    const RESULT_VAR_NAME = '‽result‽';

    private $iterationDepth = 0;
    private $currentArgPath = [];
    private $pendingSpecifiers = [];
    private $codeLines = [];
    private $haveResult = false;

    private function compilePendingSpecifiers()
    {
        if (empty($this->pendingSpecifiers)) {
            return;
        }

        $specifiers = [];
        $args = [];

        foreach ($this->pendingSpecifiers as [$specifier, $arg, $count]) {
            if ($count === null) { // scalar
                $specifiers[] = $specifier . $count;
                $args[] = $arg;
                continue;
            }

            if ($count === UNBOUNDED) { // array of unknown length
                $specifiers[] = $specifier . '*';
                $args[] = "...{$arg}";
                continue;
            }

            // bounded array
            $specifiers[] = $specifier . $count;

            for ($i = 0; $i < $count; $i++) {
                $args[] = "{$arg}[{$i}]";
            }
        }

        $this->pendingSpecifiers = [];

        $this->appendResult("\pack(" . \var_export(\implode('', $specifiers), true) . ", " . \implode(', ', $args) . ")");
    }

    public function appendSpecifier(string $specifier, int $count = null)
    {
        $this->pendingSpecifiers[] = [$specifier, $this->getCurrentArg(), $count];
    }

    public function appendCode(string ...$lines)
    {
        $this->compilePendingSpecifiers();

        \array_push($this->codeLines, ...$lines);
    }

    public function appendResult(string $expr = null)
    {
        $this->compilePendingSpecifiers();

        $hadResult = $this->haveResult;
        $operator = $this->haveResult ? '.=' : '=';
        $this->haveResult = true;

        if ($expr !== null) {
            $this->codeLines[] = '$' . self::RESULT_VAR_NAME . ' ' . $operator . ' ' . $expr . ';';
        } else if (!$hadResult) {
            $this->codeLines[] = '$' . self::RESULT_VAR_NAME . ' ' . $operator . ' "";';
        }
    }

    public function getCurrentArgPath(): array
    {
        return $this->currentArgPath;
    }

    public function setCurrentArgPath(array $path)
    {
        $oldPath = $this->currentArgPath;
        $this->currentArgPath = $path;

        return $oldPath;
    }

    public function getCurrentArg(): string
    {
        return $this->getArg($this->currentArgPath);
    }

    public function getArg(array $path): string
    {
        return !empty($path)
            ? '$' . self::ARGS_VAR_NAME . '[' . \implode('][', $path) . ']'
            : '$' . self::ARGS_VAR_NAME;
    }

    public function beginIterateCurrentArg()
    {
        $this->compilePendingSpecifiers();

        $arg = $this->getCurrentArg();

        $iterationVar = "\$‽" . ++$this->iterationDepth . "‽";
        $this->currentArgPath[] = $iterationVar;

        $this->codeLines[] = "foreach ({$arg} as {$iterationVar} => \$‽trash‽) {";
    }

    public function endIterateCurrentArg()
    {
        $this->compilePendingSpecifiers();

        $this->codeLines[] = '}';

        \array_pop($this->currentArgPath);
        $this->iterationDepth--;
    }

    public function pushArgDimension($key)
    {
        $this->currentArgPath[] = \var_export($key, true);
    }

    public function popArgDimension()
    {
        return \array_pop($this->currentArgPath);
    }

    public function getCodeLines(): array
    {
        $this->compilePendingSpecifiers();

        return $this->codeLines;
    }
}
