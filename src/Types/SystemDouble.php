<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use const DaveRandom\Pack\DOUBLE_WIDTH;

final class SystemDouble extends NumericType
{
    public function __construct()
    {
        parent::__construct(DOUBLE_WIDTH, 'd', false);
    }
}
