<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use const DaveRandom\Pack\INT_WIDTH;

final class IntSystem extends IntegerType
{
    public function __construct(int $flags = 0)
    {
        parent::__construct(INT_WIDTH, $flags);
    }
}
