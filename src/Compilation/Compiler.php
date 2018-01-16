<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

use DaveRandom\Pack\Packer;
use DaveRandom\Pack\Types\VectorType;
use DaveRandom\Pack\Unpacker;

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

    public function pack(array $‽a): string
    {
        %1$s
    }
};
';

    const UNPACKER_TEMPLATE = '
return new class($type) implements \\' . Unpacker::class . '
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

    public function unpack(string $‽d, int $‽o = 0, int &$‽c = null): array
    {
        %1$s
    }
};
';

    public function compilePackerMethodBody(VectorType $type, string $argsVarName, int $indentation = 8, int $increment = 4): string
    {
        $ctx = new Pack\CompilationContext($argsVarName);
        $type->generatePackCode($ctx, null);

        return $ctx->getCode($indentation, $increment);
    }

    public function compilePacker(VectorType $type): Packer
    {
        $class = \sprintf(self::PACKER_TEMPLATE, \trim($this->compilePackerMethodBody($type, '$‽a')));

        echo $class; // todo: remove this

        return eval($class);
    }

    public function compileUnpackerMethodBody(VectorType $type, string $dataVarName, string $offsetVarName, string $countVarName, int $indentation = 8, int $increment = 4): string
    {
        $ctx = new Unpack\CompilationContext($dataVarName, $offsetVarName, $countVarName);
        $type->generateUnpackCode($ctx, null);

        return $ctx->getCode($indentation, $increment);
    }

    public function compileUnpacker(VectorType $type): Unpacker
    {
        $class = \sprintf(self::UNPACKER_TEMPLATE, \trim($this->compileUnpackerMethodBody($type, '$‽d', '$‽o', '$‽c')));

        echo $class; // todo: remove this

        return eval($class);
    }
}
