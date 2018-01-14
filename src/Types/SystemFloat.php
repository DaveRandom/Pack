<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

use const DaveRandom\Pack\FLOAT_WIDTH;

final class SystemFloat extends NumericType
{
    public function __construct()
    {
        parent::__construct(FLOAT_WIDTH, 'f', false);
    }
}
