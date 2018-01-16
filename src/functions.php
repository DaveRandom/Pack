<?php declare(strict_types=1);

namespace DaveRandom\Pack;

const HAVE_FLOAT_ORDER = (\PHP_VERSION_ID >= 70015 && \PHP_VERSION_ID < 70100) || \PHP_VERSION_ID >= 70101;
const UNBOUNDED = -1;

\define(__NAMESPACE__ . '\\SYSTEM_LITTLE_ENDIAN', \pack(TypeCodes::UINT16_SYS, 1) === "\x01\x00");

\define(__NAMESPACE__ . '\\SYSTEM_INT_SIZE', \strlen(\pack(TypeCodes::INT_SYS, 0)));
\define(__NAMESPACE__ . '\\SYSTEM_FLOAT_SIZE', \strlen(\pack(TypeCodes::FLOAT_SYS, 0.0)));
\define(__NAMESPACE__ . '\\SYSTEM_DOUBLE_SIZE', \strlen(\pack(TypeCodes::DOUBLE_SYS, 0.0)));

\define(__NAMESPACE__ . '\\SYSTEM_INT_WIDTH', \DaveRandom\Pack\SYSTEM_INT_SIZE * 8);
\define(__NAMESPACE__ . '\\SYSTEM_FLOAT_WIDTH', \DaveRandom\Pack\SYSTEM_FLOAT_SIZE * 8);
\define(__NAMESPACE__ . '\\SYSTEM_DOUBLE_WIDTH', \DaveRandom\Pack\SYSTEM_DOUBLE_SIZE * 8);

function is_valid_name(string $name): bool
{
    return (bool)\preg_match('/[a-z_\x80-\xff][a-z0-9_\x80-\xff]*/i', $name);
}
