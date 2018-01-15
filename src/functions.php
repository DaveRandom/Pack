<?php declare(strict_types=1);

namespace DaveRandom\Pack;

const HAVE_FLOAT_ORDER = (\PHP_VERSION_ID > 70015 && \PHP_VERSION_ID < 70100) || \PHP_VERSION_ID > 70101;
const UNBOUNDED = -1;

\define(__NAMESPACE__ . '\\SYSTEM_LITTLE_ENDIAN', \pack('S', 1) === "\x01\x00");

\define(__NAMESPACE__ . '\\INT_SIZE', \strlen(\pack('i', 0)));
\define(__NAMESPACE__ . '\\FLOAT_SIZE', \strlen(\pack('f', 0.0)));
\define(__NAMESPACE__ . '\\DOUBLE_SIZE', \strlen(\pack('d', 0.0)));

\define(__NAMESPACE__ . '\\INT_WIDTH', \DaveRandom\Pack\INT_SIZE * 8);
\define(__NAMESPACE__ . '\\FLOAT_WIDTH', \DaveRandom\Pack\FLOAT_SIZE * 8);
\define(__NAMESPACE__ . '\\DOUBLE_WIDTH', \DaveRandom\Pack\DOUBLE_SIZE * 8);

function is_valid_name(string $name): bool
{
    return (bool)\preg_match('/[a-z_\x80-\xff][a-z0-9_\x80-\xff]*/i', $name);
}
