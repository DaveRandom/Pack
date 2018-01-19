<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

use DaveRandom\Pack\Compilation\Block;
use DaveRandom\Pack\Compilation\CodeElement;
use const DaveRandom\Pack\UNBOUNDED;

final class CompilationContext
{
    const RESULT_VAR_NAME = '$‽r';

    private $argsVarName;

    private $iterationDepth = 0;

    private $currentArgPath = [];

    /** @var \SplQueue */
    private $pendingPackSpecifiers;

    /** @var \SplQueue */
    private $pendingResultExpressions;

    /** @var \SplStack<Block> */
    private $blocks;

    /** @var Block */
    private $currentBlock;

    private function compilePendingPackSpecifiers()
    {
        $specifiers = [];
        $dataArgs = [];

        while ($this->pendingPackSpecifiers->count() > 0) {
            list($specifier, $arg, $count) = $this->pendingPackSpecifiers->dequeue();

            if ($count === null) { // scalar
                $specifiers[] = $specifier;
                $dataArgs[] = $arg;
                continue;
            }

            if ($count === UNBOUNDED) { // unbounded array
                $specifiers[] = $specifier . '*';
                $dataArgs[] = "...{$arg}";
                continue;
            }

            // bounded array
            $specifiers[] = $specifier . $count;

            for ($i = 0; $i < $count; $i++) {
                $dataArgs[] = "{$arg}[{$i}]";
            }
        }

        $specifiersArg = \var_export(\implode('', $specifiers), true);
        $this->pendingResultExpressions->push("\pack({$specifiersArg}, " . \implode(', ', $dataArgs) . ")");
    }

    private function compilePendingResultExpressions()
    {
        if ($this->hasPendingPackSpecifiers()) {
            $this->compilePendingPackSpecifiers();
        }

        $expressions = [];

        while ($this->pendingResultExpressions->count() > 0) {
            $expressions[] = $this->pendingResultExpressions->dequeue();
        }

        if (!empty($expressions)) {
            $this->currentBlock->appendCodeElements(new AssignmentOperation(self::RESULT_VAR_NAME, $expressions));
        }
    }

    private function beginNewBlock(Block $block)
    {
        $this->compilePendingResultExpressions();

        $this->blocks->push($block);
        $this->currentBlock = $block;
    }

    private function endCurrentBlock()
    {
        $this->compilePendingResultExpressions();

        $innerBlock = $this->blocks->pop();
        $this->currentBlock = $this->blocks->top();
        $this->currentBlock->appendElement($innerBlock);
    }

    public function __construct(string $argsVarName)
    {
        $this->argsVarName = $argsVarName;

        $this->pendingPackSpecifiers = new \SplQueue();
        $this->pendingResultExpressions = new \SplQueue();
        $this->blocks = new \SplStack();

        $this->currentBlock = new RootBlock(self::RESULT_VAR_NAME);
        $this->blocks->push($this->currentBlock);
    }

    public function hasPendingPackSpecifiers(): bool
    {
        return $this->pendingPackSpecifiers->count() > 0;
    }

    public function appendPackSpecifier(string $specifier, int $count = null, string $arg = null)
    {
        $this->pendingPackSpecifiers->enqueue([$specifier, $arg ?? $this->getCurrentArg(), $count]);
    }

    public function appendResult(string $expr)
    {
        if ($this->hasPendingPackSpecifiers()) {
            $this->compilePendingPackSpecifiers();
        }

        $this->pendingResultExpressions->push($expr);
    }

    public function appendCodeElements(CodeElement ...$elements)
    {
        $this->compilePendingResultExpressions();

        $this->currentBlock->appendCodeElements(...$elements);
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
            ? $this->argsVarName . '[' . \implode('][', $path) . ']'
            : $this->argsVarName;
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
        $arg = $this->getCurrentArg();

        $iterationLevelId = ++$this->iterationDepth;
        $keyVar = "\$‽k{$iterationLevelId}";
        $valueVar = "\$‽v{$iterationLevelId}";
        $this->currentArgPath[] = $keyVar;

        $this->beginNewBlock(new InnerBlock("foreach ({$arg} as {$keyVar} => {$valueVar})"));
    }

    public function endIterateCurrentArg()
    {
        $this->endCurrentBlock();

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
        while ($this->blocks->count() > 1) {
            $this->endCurrentBlock();
        }

        $this->compilePendingResultExpressions();

        return $this->blocks->bottom()->getCode($indentation, $increment, '=');
    }
}
