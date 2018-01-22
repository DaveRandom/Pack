<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation;

interface Compilable
{
    function compile(int $indentation, int $increment): string;
}
