<?php declare(strict_types=1);

namespace DaveRandom\Pack;

use DaveRandom\Pack\Types\Vector;

interface Packer
{
    function pack(array $args): string;
    function getDefinition(): Vector;
}
