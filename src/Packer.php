<?php declare(strict_types=1);

namespace DaveRandom\Pack;

interface Packer
{
    function pack(array $args): string;
    function getDefinition(): Vector;
}
