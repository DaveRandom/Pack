<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

use const DaveRandom\Pack\UNBOUNDED;

final class PackCompilationContext
{
    const ARGS_VAR_NAME = '‽args‽';
    const RESULT_VAR_NAME = '‽result‽';

    private $iterationDepth = 0;
    private $haveResult = false;

    private $currentArgPath = [];

    /** @var \SplQueue */
    private $pendingPackSpecifiers;

    /** @var \SplStack<Block> */
    private $blocks;

    /** @var Block */
    private $currentBlock;

    private function compilePendingSpecifiers()
    {
        $specifiers = [];
        $args = [];

        while ($this->pendingPackSpecifiers->count() > 0) {
            list($specifier, $arg, $count) = $this->pendingPackSpecifiers->dequeue();

            if ($count === null) { // scalar
                $specifiers[] = $specifier;
                $args[] = $arg;
                continue;
            }

            if ($count === UNBOUNDED) { // unbounded array
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

        if (!empty($specifiers)) {
            $this->appendResult("\pack(" . \var_export(\implode('', $specifiers), true) . ", " . \implode(', ', $args) . ")");
        }
    }

    public function __construct()
    {
        $this->pendingPackSpecifiers = new \SplQueue();
        $this->blocks = new \SplStack();

        $this->blocks->push($this->currentBlock = new Block());
    }

    public function appendSpecifier(string $specifier, int $count = null, string $arg = null)
    {
        $this->pendingPackSpecifiers->enqueue([$specifier, $arg ?? $this->getCurrentArg(), $count]);
    }

    public function appendCode(string ...$statements)
    {
        $this->compilePendingSpecifiers();

        foreach ($statements as $statement) {
            $this->currentBlock->appendStatement(new Statement($statement));
        }
    }

    public function appendResult(string $expr)
    {
        $this->compilePendingSpecifiers();

        $operator = $this->haveResult ? '.=' : '=';
        $this->haveResult = true;

        $this->currentBlock->appendStatement(new Statement('$' . self::RESULT_VAR_NAME . ' ' . $operator . ' ' . $expr . ';'));
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

    public function getCurrentArgAsBoundedArrayArgList(int $count): string
    {
        return $this->getArgAsBoundedArrayArgList($this->currentArgPath, $count);
    }

    public function getArg(array $path): string
    {
        return !empty($path)
            ? '$' . self::ARGS_VAR_NAME . '[' . \implode('][', $path) . ']'
            : '$' . self::ARGS_VAR_NAME;
    }

    public function getArgAsBoundedArrayArgList(array $path, int $count): string
    {
        $arg = $this->getArg($path);
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = "{$arg}[{$i}]";;
        }

        return \implode(', ', $result);
    }

    public function beginIterateCurrentArg()
    {
        $this->compilePendingSpecifiers();

        if (!$this->haveResult) {
            $this->currentBlock->appendStatement(new Statement('$' . self::RESULT_VAR_NAME . ' = \'\';'));
            $this->haveResult = true;
        }

        $arg = $this->getCurrentArg();

        $iterationVar = "\$‽" . ++$this->iterationDepth . "‽";
        $this->currentArgPath[] = $iterationVar;

        $this->blocks->push($this->currentBlock = new Block("foreach ({$arg} as {$iterationVar} => \$‽trash‽)"));
    }

    public function endIterateCurrentArg()
    {
        $this->compilePendingSpecifiers();

        $innerBlock = $this->blocks->pop();
        $this->currentBlock = $this->blocks->top();
        $this->currentBlock->appendBlock($innerBlock);

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

    public function getCode(int $indentation, int $increment = 4)
    {
        $this->compilePendingSpecifiers();

        return $this->blocks->bottom()->getCode($indentation, $increment);
    }
}
