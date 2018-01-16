<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class Float64 extends FloatType
{
    public function __construct(int $flags)
    {
        parent::__construct(64, (bool)($flags & self::LITTLE_ENDIAN));
    }
}
