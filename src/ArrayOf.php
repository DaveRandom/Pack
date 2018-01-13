<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: chris.wright
 * Date: 13/01/2018
 * Time: 01:04
 */

namespace DaveRandom\Pack;


final class ArrayOf implements Element
{
    private $element;
    private $bounds;

    public function __construct(Element $element, int $bounds)
    {
        $this->element = $element;
        $this->bounds = $bounds;
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count)
    {
        $this->element->generatePackCode($ctx, $count * $this->bounds);
    }
}
