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

    public function pack(array ' . PackCompilationContext::ARGS_VAR_NAME . '): string
    {
        %s
    }
};
';

    public function compilePacker(VectorType $type): Packer
    {
        $ctx = new PackCompilationContext();
        $type->generatePackCode($ctx);

        \printf(self::PACKER_TEMPLATE, \trim($ctx->getCode(8, 4)));
        return eval(\sprintf(self::PACKER_TEMPLATE, \trim($ctx->getCode(8, 4))));
    }
}
