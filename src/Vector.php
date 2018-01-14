<?php declare(strict_types=1);

namespace DaveRandom\Pack;

interface Vector extends Element, \Countable
{
    function count(): int;
    function isFinite(): bool;
}
