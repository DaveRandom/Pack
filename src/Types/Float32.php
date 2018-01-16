<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class Float32 extends FloatType
{
    public function __construct(int $flags)
    {
        parent::__construct(32, (bool)($flags & self::LITTLE_ENDIAN));
    }
}
