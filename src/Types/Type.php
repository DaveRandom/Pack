<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\CompilationContext as PackCompilationContext;

interface Type
{
    function generatePackCode(PackCompilationContext $context, int $count = null);
    function isFixedSize(): bool;
    function getSize(): int;
}
