<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\PackCompilationContext;

interface Type
{
    function generatePackCode(PackCompilationContext $context, int $count = null);
}
