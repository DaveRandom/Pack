<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class UInt8 extends IntegerType
{
    public function __construct(int $flags = 0)
    {
        parent::__construct(8, $flags | parent::UNSIGNED);
    }
}
