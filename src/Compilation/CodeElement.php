<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

interface CodeElement
{
    function getCode(int $indentation, int $increment): string;
}
