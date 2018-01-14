<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class UInt32 extends IntegerType
{
    public function __construct(int $flags = 0)
    {
        parent::__construct(32, $flags | parent::UNSIGNED);
    }
}
