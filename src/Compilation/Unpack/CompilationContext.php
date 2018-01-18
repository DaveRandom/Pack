<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

use const DaveRandom\Pack\UNBOUNDED;

final class CompilationContext
{
    const RESULT_VAR_NAME = '$‽r';
    const STRLEN_VAR_NAME = '$‽l';

    private $dataVarName;
    private $offsetVarName;
    private $countVarName;

    /** @var CodeElement[] */
    private $codeElements = [];

    private $currentTargetPath = [];

    private $pendingUnpackSpecifiers;

    private $totalSize = 0;

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

            $codeElements[] = $block = new InnerBlock("for (\$‽i = 1; isset(\$‽u[\$‽k = \"{$elementKey}{\$‽i}\"]); \$‽i++)");
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
        $this->totalSize += $consumed;

        $this->appendCodeElements(
            $this->generateLengthCheckBlock($consumed, $firstTargetPath),
            new Statement("\$‽u = \unpack({$specifierString}, {$this->dataVarName}, {$this->offsetVarName});"),
            ...$codeElements
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

    public function appendUnpackSpecifier(string $specifier, int $size, int $count = null, array $targetPath = null)
    {
        $this->pendingUnpackSpecifiers->push([$specifier, $size, $count, $targetPath ?? $this->currentTargetPath]);
    }

    private function generateLengthCheckBlock(int $length, array $targetPath = null): Block
    {
        $target = \var_export("'" . \implode('/', \array_map(function($level) {
            return eval("return (string){$level};");
        }, $targetPath ?? $this->currentTargetPath)) . "'", true);

        return (new InnerBlock("if ({$this->offsetVarName} + {$length} > " . self::STRLEN_VAR_NAME . ")"))
            ->appendCodeElements(new Statement(
                "throw new \InvalidArgumentException(\sprintf("
                . "'Insufficient data input to decode elements from path %s at offset %d: need %d, have %d',"
                . " {$target}, {$this->offsetVarName}, {$length}, " . self::STRLEN_VAR_NAME . " - {$this->offsetVarName}"
                . "));"
            ));
    }

    public function appendResult(string $expr, int $length)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $this->appendCodeElements(
            $this->generateLengthCheckBlock($length),
            new Statement("{$this->getCurrentTarget()} = {$expr};"),
            new Statement("{$this->offsetVarName} += {$length};")
        );

        $this->totalSize += $length;
    }

    public function appendResultWithCount(string $expr, int $size)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $this->appendCodeElements(
            new Statement("{$this->getCurrentTarget()} = {$expr};"),
            new Statement("{$this->offsetVarName} += \count({$this->getCurrentTarget()}) * {$size};"),
            new Statement("{$this->countVarName} += \count({$this->getCurrentTarget()}) * {$size};")
        );
    }

    public function appendCodeElements(CodeElement ...$elements)
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        foreach ($elements as $element) {
            $this->codeElements[] = $element;
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

    public function getCodeElements(int $indentation, int $increment): string
    {
        if ($this->hasPendingUnpackSpecifiers()) {
            $this->compilePendingUnpackSpecifiers();
        }

        $result
            = (new Statement(self::RESULT_VAR_NAME . " = [];"))->getCode($indentation, $increment)
            . (new Statement(self::STRLEN_VAR_NAME . " = \strlen({$this->dataVarName});"))->getCode($indentation, $increment)
        ;

        foreach ($this->codeElements as $element) {
            $result .= $element->getCode($indentation, $increment);
        }

        $result
            .= (new Statement("{$this->countVarName} += {$this->totalSize};"))->getCode($indentation, $increment)
            . (new Statement("return " . self::RESULT_VAR_NAME . ";"))->getCode($indentation, $increment)
        ;

        return $result;
    }
}
