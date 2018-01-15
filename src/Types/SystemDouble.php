<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class SystemDouble extends NumericType
{
    public function __construct()
    {
        parent::__construct(\DaveRandom\Pack\DOUBLE_WIDTH, 'd', false);
    }
}
