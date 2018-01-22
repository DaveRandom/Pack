<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use DaveRandom\Pack\Compilation\Pack\Method as PackMethod;
use DaveRandom\Pack\Compilation\Unpack\Method as UnpackMethod;

interface Type
{
    function generatePackCode(PackMethod $method, int $count = null);
    function generateUnpackCode(UnpackMethod $method, int $count = null);
    function isFixedSize(): bool;
    function getSize(): int;
}
