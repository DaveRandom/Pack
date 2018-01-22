<?php declare(strict_types=1);

namespace DaveRandom\Pack;

use DaveRandom\Pack\Compilation\Compiler;
use DaveRandom\Pack\Types\ArrayOf;
use DaveRandom\Pack\Types\Int16;
use DaveRandom\Pack\Types\Int32;
use DaveRandom\Pack\Types\SpacePaddedString;
use DaveRandom\Pack\Types\Struct;
use DaveRandom\Pack\Types\UInt32;
use DaveRandom\Pack\Types\UInt8;

require __DIR__ . '/../vendor/autoload.php';

$struct = new ArrayOf(new Struct([
    'one' => new Int32(),
    'two' => new ArrayOf(new UInt8(), 3),
    'three' => new Struct([
        'one' => new UInt32(),
        'two' => new ArrayOf(new Int16(), 8),
    ]),
    'str' => new ArrayOf(new SpacePaddedString(16), 6)
]));

$data = [
    [
        'one' => 65,
        'two' => [66, 67, 68],
        'three' => [
            'one' => 69,
            'two' => [70, 71, 72, 73, 74, 75, 76, 77],
        ],
        'str' => ['Hello', 'World!', 'Nice', 'To', 'Be', 'Here'],
    ],
    [
        'one' => 65,
        'two' => [66, 67, 68],
        'three' => [
            'one' => 69,
            'two' => [70, 71, 72, 73, 74, 75, 76, 77],
        ],
        'str' => ['Hello', 'World!', 'Nice', 'To', 'Be', 'Here'],
    ],
];

$compiler = new Compiler;

$packer = $compiler->compilePacker($struct);
$packed = $packer->pack($data);
var_dump($packed);

$unpacker = $compiler->compileUnpacker($struct);
$unpacked = $unpacker->unpack(\substr($packed, 0), 0, $consumed);
var_dump($consumed, $unpacked === $data, $unpacked);
