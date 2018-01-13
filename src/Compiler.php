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

    public function getDefinition(): \\' . Element::class . '
    {
        return $this->definition;
    }

    public function pack(array $args): string
    {
        %s
        return $result;
    }
};
';

    public function compilePacker(Element $element): Packer
    {
        $ctx = new PackCompilationContext();
        $element->generatePackCode($ctx, 1);

        $code = '';

        foreach ($ctx->getCodeLines() as $line) {
            $code .= "\n" . \rtrim("        {$line}");
        }

        \printf(self::PACKER_TEMPLATE, \ltrim($code));
        return eval(\sprintf(self::PACKER_TEMPLATE, \ltrim($code)));
    }
}
