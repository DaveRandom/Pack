<?php declare(strict_types=1);

namespace DaveRandom\Pack;

use DaveRandom\Pack\Types\VectorType;

interface Unpacker
{
    function unpack(string $data, int $offset = 0, int &$count = 0): array;
    function getDefinition(): VectorType;
}
