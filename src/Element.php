<?php declare(strict_types=1);

namespace DaveRandom\Pack;

interface Element
{
    function generatePackCode(PackCompilationContext $context, int $count = null);
}
