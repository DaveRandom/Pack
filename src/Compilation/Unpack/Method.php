<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

use DaveRandom\Pack\Compilation\Block;
use DaveRandom\Pack\Compilation\Compilable;
use DaveRandom\Pack\Compilation\Statement;
use const DaveRandom\Pack\UNBOUNDED;

final class Method implements Compilable
{
    const RESULT_VAR_NAME = '$‽r';
    const STRLEN_VAR_NAME = '$‽l';

    private $dataVarName;
    private $offsetVarName;
    private $countVarName;

    private $currentTargetPath = [];

    private $pendingUnpackSpecifiers;

    private $currentBlock;
    private $blocks;
    private $counterVarStack;

    private function compilePendingUnpackSpecifiers()
    {
        $specifiers = [];
        $codeElements = [];
        $consumed = 0;
        $haveUnboundedTarget = false;
        $firstTargetPath = null;

        for ($i = 0; $this->pendingUnpackSpecifiers->count() > 0; $i++) {
            list($specifier, $size, $count, $targetPath) = $this->pendingUnpackSpecifiers->dequeue();
            $target = $this->getTarget($targetPath);
            $firstTargetPath = $firstTargetPath ?? $targetPath;

            $elementKey = "i{$i}";

            if ($count === null) { // scalar
                $specifiers[] = "{$specifier}{$elementKey}";
                $codeElements[] = new Statement("{$target} = \$‽u['{$elementKey}'];");
                $consumed += $size;
                continue;
            }

            if ($count !== UNBOUNDED) { // bounded array
                $specifiers[] = "{$specifier}{$count}{$elementKey}";
                $unpackedElements = [];
                for ($j = 1; $j <= $count; $j++) {
                    $unpackedElements[] = "\$‽u['{$elementKey}{$j}']";
                }
                $codeElements[] = new Statement("{$target} = [" . \implode(', ', $unpackedElements) . "];");
                $consumed += $size * $count;
                continue;
            }

            // unbounded array
            $specifiers[] = "{$specifier}*{$elementKey}";

            $codeElements[] = $block = new InnerBlock($this->countVarName, "for (\$‽i = 1; isset(\$‽u[\$‽k = \"{$elementKey}{\$‽i}\"]); \$‽i++)");
            $block->appendCodeElements(new Statement("{$target}[] = \$‽u[\$‽k];"));
            $sizeExpr = $size === 1 ? "\$‽i - 1" : "((\$‽i - 1) * {$size})";
            $codeElements[] = new Statement("{$this->countVarName} += {$sizeExpr};");
            $codeElements[] = new Statement("{$this->offsetVarName} += {$consumed} + {$sizeExpr};");
            $haveUnboundedTarget = true;
        }

        if (!$haveUnboundedTarget) {
            $codeElements[] = new Statement("{$this->offsetVarName} += {$consumed};");
        }

        $specifierString = \var_export(\implode('/', $specifiers), true);
        $this->currentBlock->addSize($consumed);

        $this->appendLengthCheck($consumed, $firstTargetPath);
        $this->currentBlock->appendCodeElements(
            new Statement("\$‽u = \unpack({$specifierString}, {$this->dataVarName}, {$this->offsetVarName});"),
            ...$codeElements
        );
    }

    private function beginNewBlock(Block $block)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $this->blocks->push($block);
        $this->currentBlock = $block;
    }

    private function endCurrentBlock()
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $innerBlock = $this->blocks->pop();
        $this->currentBlock = $this->blocks->top();
        $this->currentBlock->appendCodeElements($innerBlock);
    }

    private function getCounterVar(bool $pushTargetDimension)
    {
        $result = '$‽i' . $this->counterVarStack->count();
        $this->counterVarStack->push($pushTargetDimension);

        if ($pushTargetDimension) {
            $this->currentTargetPath[] = $result;
        }

        return $result;
    }

    private function releaseCounterVar()
    {
        if ($this->counterVarStack->pop()) {
            \array_pop($this->currentTargetPath);
        }
    }

    public function __construct(string $dataVarName, string $offsetVarName, string $countVarName)
    {
        $this->dataVarName = $dataVarName;
        $this->offsetVarName = $offsetVarName;
        $this->countVarName = $countVarName;

        $this->pendingUnpackSpecifiers = new \SplQueue();
        $this->blocks = new \SplStack();
        $this->counterVarStack = new \SplStack();

        $this->blocks->push($this->currentBlock = (new RootBlock($this->countVarName))->appendCodeElements(
            new Statement(self::RESULT_VAR_NAME . " = [];"),
            new Statement(self::STRLEN_VAR_NAME . " = \strlen({$this->dataVarName});")
        ));
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

    public function appendUnpackSpecifier(string $specifier, int $size, int $count = null, array $targetPath = null)
    {
        $this->pendingUnpackSpecifiers->push([$specifier, $size, $count, $targetPath ?? $this->currentTargetPath]);
    }

    public function appendLengthCheck($expr, array $targetPath = null)
    {
        $target = \implode(" . '/' . ", $targetPath ?? $this->currentTargetPath);

        $this->appendCodeElements(
            (new InnerBlock($this->countVarName, "if ({$this->offsetVarName} + ({$expr}) > " . self::STRLEN_VAR_NAME . ")"))
                ->appendCodeElements(new Statement(
                    "throw new \InvalidArgumentException(\sprintf("
                    . "'Insufficient data input to decode elements from path %s at offset %d: need %d, have %d',"
                    . " {$target}, {$this->offsetVarName}, {$expr}, " . self::STRLEN_VAR_NAME . " - {$this->offsetVarName}"
                    . "));"
                ))
        );
    }

    public function appendResult(string $expr, int $size)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $this->appendLengthCheck($size);
        $this->appendCodeElements(new Statement("{$this->getCurrentTarget()} = {$expr};"));
        $this->advanceDataOffset($size);

        $this->currentBlock->addSize($size);
    }

    public function appendResultWithCount(string $expr, int $size)
    {
        $this->appendResultWithSizeExpr($expr, "\count({$this->getCurrentTarget()}) * {$size}");
    }

    public function appendResultWithSizeExpr(string $expr, string $sizeExpr)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $this->appendCodeElements(new Statement("{$this->getCurrentTarget()} = {$expr};"));
        $this->advanceDataOffset($sizeExpr, true);
    }

    public function appendCodeElements(Compilable ...$elements)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $this->currentBlock->appendCodeElements(...$elements);
    }

    public function advanceDataOffset($expr, bool $advanceCount = false)
    {
        $this->appendCodeElements(new Statement("{$this->offsetVarName} += {$expr};"));

        if ($advanceCount) {
            $this->appendCodeElements(new Statement("{$this->countVarName} += {$expr};"));
        }
    }

    public function beginIterateCounter($iterationsExpr, bool $pushTargetDimension)
    {
        $counterVarName = $this->getCounterVar($pushTargetDimension);
        $loopHead = \sprintf('for (%1$s = 0; %1$s < (%2$s); %1$s++)', $counterVarName, $iterationsExpr);

        $this->beginNewBlock(new InnerBlock($this->countVarName, $loopHead));
    }

    public function endIterateCounter()
    {
        $this->endCurrentBlock();
        $this->releaseCounterVar();
    }

    public function beginConsumeRemainingData()
    {
        $counterVarName = $this->getCounterVar(true);
        $loopHead = \sprintf('for (%1$s = 0; %2$s < %3$s; %1$s++)', $counterVarName, $this->offsetVarName, self::STRLEN_VAR_NAME);

        $this->beginNewBlock(new InnerBlock($this->countVarName, $loopHead));
    }

    public function endConsumeRemainingData()
    {
        $this->endCurrentBlock();
        $this->releaseCounterVar();
    }

    public function pushTargetDimension($key)
    {
        $this->currentTargetPath[] = \var_export($key, true);
    }

    public function popTargetDimension()
    {
        return \array_pop($this->currentTargetPath);
    }

    public function addSize(int $size)
    {
        $this->currentBlock->addSize($size);
    }

    public function compile(int $indentation, int $increment): string
    {
        while ($this->blocks->count() > 1) {
            $this->endCurrentBlock();
        }

        return $this->currentBlock->compile($indentation, $increment)
            . \str_repeat(' ', $indentation) . "return " . self::RESULT_VAR_NAME . ";\n";
    }
}
