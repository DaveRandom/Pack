<?php declare(strict_types=1);

namespace DaveRandom\Pack;

interface Type
{
    function generatePackCode(PackCompilationContext $context, int $count = null);
}
