<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

interface VectorType extends Type, \Countable
{
    function count(): int;
    function isFinite(): bool;
}
