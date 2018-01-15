<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\PackCompilationContext;

interface ScalarType extends Type
{
    function generatePackCodeForExpression(PackCompilationContext $ctx, string $expr);
}
