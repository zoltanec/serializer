<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Address.php';
require __DIR__.'/House.php';
require __DIR__.'/Profile.php';

$params = [
    'name'      => 'My name',
    'surnameIn' => null,
    'address'   => [
        [
            'address' => 'test',
            'house'   => null,
        ],
    ],
];


$serializer = new Jaddek\Serializer\Serializer();
/** @var Profile $profile */
$profile    = $serializer->denormalize($params, Profile::class);

$reverted = $serializer->normalize($profile);

if ($params == $reverted) {
    echo "Array are equals\n";
}