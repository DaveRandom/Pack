<?php declare(strict_types=1);

namespace DaveRandom\Pack;

use DaveRandom\Pack\Compilation\Compiler;
use DaveRandom\Pack\Types\ArrayOf;
use DaveRandom\Pack\Types\Struct;
use DaveRandom\Pack\Types\UInt32;
use DaveRandom\Pack\Types\UInt8;

require __DIR__ . '/../vendor/autoload.php';

$struct = new Struct([
    'one' => new UInt32(),
    'two' => new ArrayOf(new UInt8(), 3),
    'three' => new Struct([
        'one' => new UInt32(),
        'two' => new ArrayOf(new UInt8(), 3),
    ]),
]);

$unpacker = (new Compiler)->compileUnpacker($struct);
$data = $unpacker->unpack("\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x00\x00\x00\x01\x02\x03\x04", 2, $consumed);

var_dump($data['three']['two'][1], $consumed);
