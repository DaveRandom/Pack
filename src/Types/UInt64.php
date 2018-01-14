<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class UInt64 extends IntegerType
{
    public function __construct(int $flags = 0)
    {
        parent::__construct(64, $flags | parent::UNSIGNED);
    }
}
