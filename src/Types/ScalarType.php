<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;

interface ScalarType extends Type
{
    function generatePackCodeForExpression(PackMethod $method, string $expression);
    function generateUnpackCodeForSingleValueAtCurrentOffset(UnpackMethod $method, string $targetVariable);
}
