<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class Struct implements Element
{
    /** @var Element[] */
    private $elements = [];

    /**
     * @param Element[] $elements
     */
    public function __construct(array $elements)
    {
        foreach ($elements as $name => $element) {
            if (!\preg_match('/[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*/i', $name)) {
                throw new \InvalidArgumentException("Invalid struct element name: {$name}");
            }

            if (!$element instanceof Element) {
                throw new \InvalidArgumentException(Struct::class . ' may only contain instances of ' . Element::class);
            }
        }

        $this->elements = $elements;
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count)
    {
        foreach ($this->elements as $element) {
            $element->generatePackCode($ctx, 1);
        }
    }
}
