<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class SystemFloat extends NumericType
{
    public function __construct()
    {
        parent::__construct(\DaveRandom\Pack\FLOAT_WIDTH, 'f', false);
    }
}
