<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class UInt16 extends IntegerType
{
    public function __construct(int $flags = 0)
    {
        parent::__construct(16, $flags | parent::UNSIGNED);
    }
}
