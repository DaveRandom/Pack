<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class SystemInt extends IntegerType
{
    public function __construct(int $flags = self::SYSTEM_TYPE_DEFAULT_FLAGS)
    {
        parent::__construct(\DaveRandom\Pack\SYSTEM_INT_WIDTH, $flags);
    }
}
