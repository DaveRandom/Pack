<?php declare(strict_types=1);

namespace DaveRandom\Pack;

use DaveRandom\Enum\Enum;

final class TypeCodes extends Enum
{
    const NULL_PADDED_STRING  = 'a';
    const SPACE_PADDED_STRING = 'A';

    const HEX_STRING          = 'H'; // Hex string, high nibble first
    const HEX_STRING_LE       = 'h'; // Hex string, low nibble first

    const INT_SYS             = 'i'; // signed integer (machine dependent size and byte order)
    const UINT_SYS            = 'I'; // unsigned integer (machine dependent size and byte order)

    const INT8                = 'c'; // signed char
    const UINT8               = 'C'; // unsigned char

    const INT16_SYS           = 's'; // signed short (always 16 bit, machine byte order)
    const UINT16              = 'n'; // unsigned short (always 16 bit, big endian byte order)
    const UINT16_LE           = 'v'; // unsigned short (always 16 bit, little endian byte order)
    const UINT16_SYS          = 'S'; // unsigned short (always 16 bit, machine byte order)

    const INT32_SYS           = 'l'; // signed long (always 32 bit, machine byte order)
    const UINT32              = 'N'; // unsigned long (always 32 bit, big endian byte order)
    const UINT32_LE           = 'V'; // unsigned long (always 32 bit, little endian byte order)
    const UINT32_SYS          = 'L'; // unsigned long (always 32 bit, machine byte order)

    const INT64_SYS           = 'q'; // signed long long (always 64 bit, machine byte order)
    const UINT64              = 'J'; // unsigned long long (always 64 bit, big endian byte order)
    const UINT64_LE           = 'P'; // unsigned long long (always 64 bit, little endian byte order)
    const UINT64_SYS          = 'Q'; // unsigned long long (always 64 bit, machine byte order)

    const FLOAT               = 'G'; // float (machine dependent size, big endian byte order)
    const FLOAT_LE            = 'g'; // float (machine dependent size, little endian byte order)
    const FLOAT_SYS           = 'f'; // float (machine dependent size and representation)

    const DOUBLE              = 'E'; // double (machine dependent size, big endian byte order)
    const DOUBLE_LE           = 'e'; // double (machine dependent size, little endian byte order)
    const DOUBLE_SYS          = 'd'; // double (machine dependent size and representation)
}
