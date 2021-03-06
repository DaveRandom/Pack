<?php declare(strict_types=1);

namespace DaveRandom\Pack;

use DaveRandom\Pack\Compilation\Compiler;
use DaveRandom\Pack\Types\ArrayOf;
use DaveRandom\Pack\Types\Int16;
use DaveRandom\Pack\Types\Int32;
use DaveRandom\Pack\Types\IntegerType;
use DaveRandom\Pack\Types\LengthPrefixedString;
use DaveRandom\Pack\Types\NullTerminatedString;
use DaveRandom\Pack\Types\Struct;
use DaveRandom\Pack\Types\UInt16;
use DaveRandom\Pack\Types\UInt32;
use DaveRandom\Pack\Types\UInt8;

require __DIR__ . '/../vendor/autoload.php';

$struct = new Struct([
    'one' => new UInt32(),
    'two' => new ArrayOf(
        new Struct([
            'array'  => new ArrayOf(new UInt8, 7),
            'field1' => new UInt32,
            'field2' => new Int32(IntegerType::LITTLE_ENDIAN),
            'field3' => new Int32,
            'substruct' => new Struct([
                'u161' => new UInt16(),
                'i16' => new Int16(),
                'u162' => new UInt32(),
                'u163' => new UInt16(),
            ]),
            'array2'  => new ArrayOf(new UInt8, 7),
        ])
    , 3),
    'strings' => new ArrayOf(new LengthPrefixedString(new UInt16()), 3),
//    'strings' => new ArrayOf(new NullTerminatedString()),
//    'end' => new UInt8(),
]);

$packer = (new Compiler)->compilePacker($struct);

var_dump($packer->pack([
    'one' => 0x61626364,
    'two' => [
        [
            'array' => [0x54, 0x68, 0x69, 0x73, 0x20, 0x63, 0x6f],
            'field1' => 0x64652073,
            'field2' => 0x736b6375,
            'field3' => 0x20617373,
            'substruct' => [
                'u161' => 0x2061,
                'i16' => 0x6161,
                'u162' => 0x6161,
                'u163' => 0x6120,
            ],
            'array2' => [0x54, 0x68, 0x69, 0x73, 0x20, 0x63, 0x6f],
        ],
        [
            'array' => [0x54, 0x68, 0x69, 0x73, 0x20, 0x63, 0x6f],
            'field1' => 0x64652073,
            'field2' => 0x736b6375,
            'field3' => 0x20617373,
            'substruct' => [
                'u161' => 0x2061,
                'i16' => 0x6161,
                'u162' => 0x6161,
                'u163' => 0x6120,
            ],
            'array2' => [0x54, 0x68, 0x69, 0x73, 0x20, 0x63, 0x6f],
        ],
        [
            'array' => [0x54, 0x68, 0x69, 0x73, 0x20, 0x63, 0x6f],
            'field1' => 0x64652073,
            'field2' => 0x736b6375,
            'field3' => 0x20617373,
            'substruct' => [
                'u161' => 0x2061,
                'i16' => 0x6161,
                'u162' => 0x6161,
                'u163' => 0x6120,
            ],
            'array2' => [0x54, 0x68, 0x69, 0x73, 0x20, 0x63, 0x6f],
        ],
    ],
    'strings' => ['1234567890', '1234567890', '1234567890'],
    'end' => 65,
]));
