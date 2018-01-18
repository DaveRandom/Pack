<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

interface CodeElement
{
    function getCode(int $indentation, int $increment): string;
}
