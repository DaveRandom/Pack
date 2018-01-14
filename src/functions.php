<?php declare(strict_types=1);

namespace DaveRandom\Pack;

const HAVE_FLOAT_ORDER = (\PHP_VERSION_ID > 70015 && \PHP_VERSION_ID < 70100) || \PHP_VERSION_ID > 70101;
const UNBOUNDED = -1;

\define(__NAMESPACE__ . '\\SYSTEM_LITTLE_ENDIAN', \pack('S', 1) === "\x01\x00");

\define(__NAMESPACE__ . '\\INT_SIZE_BYTES', \strlen(\pack('i', 0)));
\define(__NAMESPACE__ . '\\FLOAT_SIZE_BYTES', \strlen(\pack('f', 0.0)));
\define(__NAMESPACE__ . '\\DOUBLE_SIZE_BYTES', \strlen(\pack('d', 0.0)));

\define(__NAMESPACE__ . '\\INT_SIZE', INT_SIZE_BYTES * 8);
\define(__NAMESPACE__ . '\\FLOAT_SIZE', FLOAT_SIZE_BYTES * 8);
\define(__NAMESPACE__ . '\\DOUBLE_SIZE', DOUBLE_SIZE_BYTES * 8);

\define(__NAMESPACE__ . '\\ELEMENT_SIZES_BYTES', [
    Types::INT_SYS    => INT_SIZE_BYTES,
    Types::UINT_SYS   => INT_SIZE_BYTES,

    Types::INT8       => 1,
    Types::UINT8      => 1,

    Types::INT16_SYS  => 2,
    Types::UINT16     => 2,
    Types::UINT16_LE  => 2,
    Types::UINT16_SYS => 2,

    Types::INT32_SYS  => 4,
    Types::UINT32     => 4,
    Types::UINT32_LE  => 4,
    Types::UINT32_SYS => 4,

    Types::INT64_SYS  => 8,
    Types::UINT64     => 8,
    Types::UINT64_LE  => 8,
    Types::UINT64_SYS => 8,

    Types::FLOAT      => FLOAT_SIZE_BYTES,
    Types::FLOAT_LE   => FLOAT_SIZE_BYTES,
    Types::FLOAT_SYS  => FLOAT_SIZE_BYTES,

    Types::DOUBLE     => DOUBLE_SIZE_BYTES,
    Types::DOUBLE_LE  => DOUBLE_SIZE_BYTES,
    Types::DOUBLE_SYS => DOUBLE_SIZE_BYTES,
]);

const INT_BY_SIZE = [
    8  => Types::INT8,
    16 => Types::INT16_SYS,
    32 => Types::INT32_SYS,
    64 => Types::INT64_SYS,
];

const UINT_BY_SIZE = [
    8  => Types::UINT8,
    16 => Types::UINT16,
    32 => Types::UINT32,
    64 => Types::UINT64,
];

const UINT_LE_BY_SIZE = [
    16 => Types::UINT16_LE,
    32 => Types::UINT32_LE,
    64 => Types::UINT64_LE,
];
