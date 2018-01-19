<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use DaveRandom\Pack\Compilation\Unpack\CompilationContext as UnpackCompilationContext;

interface ScalarType extends Type
{
    function generatePackCodeForExpression(PackCompilationContext $context, string $expression);
    function generateUnpackCodeForSingleValueAtCurrentOffset(UnpackCompilationContext $context, string $targetVariable);
}
