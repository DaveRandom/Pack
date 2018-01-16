<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

use DaveRandom\Pack\Packer;
use DaveRandom\Pack\Types\VectorType;

final class Compiler
{
    const PACKER_TEMPLATE = '
return new class($type) implements \\' . Packer::class . '
{
    private $definition;
    
    public function __construct($definition)
    {
        $this->definition = $definition;
    }

    public function getDefinition(): \\' . VectorType::class . '
    {
        return $this->definition;
    }

    public function pack(array $‽args‽): string
    {
        %1$s
    }
};
';

    public function compilePackerMethodBody(VectorType $type, string $argsVarName, int $indentation = 8, int $increment = 4): string
    {
        $ctx = new Pack\CompilationContext($argsVarName);
        $type->generatePackCode($ctx);

        return $ctx->getCode($indentation, $increment);
    }

    public function compilePacker(VectorType $type): Packer
    {
        $class = \sprintf(self::PACKER_TEMPLATE, \trim($this->compilePackerMethodBody($type, '$‽args‽')));

        echo $class; // todo: remove this

        return eval($class);
    }
}
