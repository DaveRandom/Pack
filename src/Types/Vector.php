<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

interface Vector extends Type, \Countable
{
    function count(): int;
    function isFinite(): bool;
}
