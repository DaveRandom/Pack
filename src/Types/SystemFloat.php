<?php declare(strict_types=1);

namespace DaveRandom\Pack\Types;

final class SystemFloat extends FloatType
{
    public function __construct(int $flags = self::SYSTEM_TYPE_DEFAULT_FLAGS)
    {
        parent::__construct(\DaveRandom\Pack\SYSTEM_FLOAT_WIDTH, (bool)($flags & self::LITTLE_ENDIAN));
    }
}
