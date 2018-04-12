<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Address.php';
require __DIR__.'/House.php';
require __DIR__.'/Profile.php';

$params = [
    'name'      => 'My name',
    'surnameIn' => 'Sur name',
    'address'   => [
        [
            'address' => 'test',
            'house'  => [
                'building' => 'hello',
            ],
        ],
    ],
];


$serializer = new Jaddek\Serializer\Serializer();
$profile    = $serializer->denormalize($params, Profile::class);

$reverted = $serializer->normalize($profile);

if ($params == $reverted) {
    echo "Array are equals\n";
}