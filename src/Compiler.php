<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class Compiler
{
    const PACKER_TEMPLATE = '
return new class($element) implements \\' . Packer::class . '
{
    private $definition;
    
    public function __construct($definition)
    {
        $this->definition = $definition;
    }

    public function getDefinition(): \\' . Vector::class . '
    {
        return $this->definition;
    }

    public function pack(array $' . PackCompilationContext::ARGS_VAR_NAME . '): string
    {
        %s
        return $' . PackCompilationContext::RESULT_VAR_NAME . ';
    }
};
';

    public function compilePacker(Vector $element): Packer
    {
        $ctx = new PackCompilationContext();
        $element->generatePackCode($ctx);

        $code = '';

        foreach ($ctx->getCodeLines() as $line) {
            $code .= "\n" . \rtrim("        {$line}");
        }

        \printf(self::PACKER_TEMPLATE, \ltrim($code));
        return eval(\sprintf(self::PACKER_TEMPLATE, \ltrim($code)));
    }
}
