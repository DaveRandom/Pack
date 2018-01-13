<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class Int64 extends Integer
{
    public function __construct(int $flags = 0)
    {
        parent::__construct(64, $flags | parent::SIGNED);
    }
}
