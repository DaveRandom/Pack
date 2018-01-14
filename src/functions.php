<?php declare(strict_types=1);

namespace DaveRandom\Pack;

const HAVE_FLOAT_ORDER = (\PHP_VERSION_ID > 70015 && \PHP_VERSION_ID < 70100) || \PHP_VERSION_ID > 70101;
const UNBOUNDED = -1;

\define(__NAMESPACE__ . '\\SYSTEM_LITTLE_ENDIAN', \pack('S', 1) === "\x01\x00");

\define(__NAMESPACE__ . '\\INT_SIZE', \strlen(\pack('i', 0)));
\define(__NAMESPACE__ . '\\FLOAT_SIZE', \strlen(\pack('f', 0.0)));
\define(__NAMESPACE__ . '\\DOUBLE_SIZE', \strlen(\pack('d', 0.0)));

\define(__NAMESPACE__ . '\\INT_WIDTH', INT_SIZE * 8);
\define(__NAMESPACE__ . '\\FLOAT_WIDTH', FLOAT_SIZE * 8);
\define(__NAMESPACE__ . '\\DOUBLE_WIDTH', DOUBLE_SIZE * 8);

const INT_CODES_BY_WIDTH = [
    8  => TypeCodes::INT8,
    16 => TypeCodes::INT16_SYS,
    32 => TypeCodes::INT32_SYS,
    64 => TypeCodes::INT64_SYS,
];

const UINT_CODES_BY_WIDTH = [
    8  => TypeCodes::UINT8,
    16 => TypeCodes::UINT16,
    32 => TypeCodes::UINT32,
    64 => TypeCodes::UINT64,
];

const UINT_LE_CODES_BY_WIDTH = [
    16 => TypeCodes::UINT16_LE,
    32 => TypeCodes::UINT32_LE,
    64 => TypeCodes::UINT64_LE,
];

function is_valid_name(string $name): bool
{
    return (bool)\preg_match('/[a-z_\x80-\xff][a-z0-9_\x80-\xff]*/i', $name);
}
