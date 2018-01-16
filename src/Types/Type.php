<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;
use DaveRandom\Pack\Compilation\Unpack\CompilationContext as UnpackCompilationContext;

interface Type
{
    function generatePackCode(PackCompilationContext $context, int $count = null);
    function generateUnpackCode(UnpackCompilationContext $context, int $count = null);
    function isFixedSize(): bool;
    function getSize(): int;
}
