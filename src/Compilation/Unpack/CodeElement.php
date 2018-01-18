<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

interface CodeElement
{
    function getCode(int $indentation, int $increment): string;
}
