<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class SystemUInt extends IntegerType
{
    public function __construct(int $flags = 0)
    {
        parent::__construct(\DaveRandom\Pack\INT_WIDTH, $flags | self::UNSIGNED);
    }
}
