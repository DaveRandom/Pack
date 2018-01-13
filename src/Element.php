<?php declare(strict_types=1);

namespace DaveRandom\Pack;

interface Element
{
    const REPEAT = -1;

    function generatePackCode(PackCompilationContext $context, int $count);
}
