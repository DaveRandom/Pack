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

\define(__NAMESPACE__ . '\\ELEMENT_SIZES', [
    TypeCodes::INT_SYS    => INT_SIZE,
    TypeCodes::UINT_SYS   => INT_SIZE,

    TypeCodes::INT8       => 1,
    TypeCodes::UINT8      => 1,

    TypeCodes::INT16_SYS  => 2,
    TypeCodes::UINT16     => 2,
    TypeCodes::UINT16_LE  => 2,
    TypeCodes::UINT16_SYS => 2,

    TypeCodes::INT32_SYS  => 4,
    TypeCodes::UINT32     => 4,
    TypeCodes::UINT32_LE  => 4,
    TypeCodes::UINT32_SYS => 4,

    TypeCodes::INT64_SYS  => 8,
    TypeCodes::UINT64     => 8,
    TypeCodes::UINT64_LE  => 8,
    TypeCodes::UINT64_SYS => 8,

    TypeCodes::FLOAT      => FLOAT_SIZE,
    TypeCodes::FLOAT_LE   => FLOAT_SIZE,
    TypeCodes::FLOAT_SYS  => FLOAT_SIZE,

    TypeCodes::DOUBLE     => DOUBLE_SIZE,
    TypeCodes::DOUBLE_LE  => DOUBLE_SIZE,
    TypeCodes::DOUBLE_SYS => DOUBLE_SIZE,
]);

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
