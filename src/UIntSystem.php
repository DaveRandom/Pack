<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class UIntSystem extends IntegerType
{
    public function __construct(int $flags = 0)
    {
        parent::__construct(INT_WIDTH, $flags | self::UNSIGNED);
    }
}
