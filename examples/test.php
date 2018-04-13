<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Address.php';
require __DIR__.'/House.php';
require __DIR__.'/Profile.php';

$params = [
    'name'      => 'My name',
    'surnameIn' => 'Sur name',
    'address'   => null,
];


$serializer = new Jaddek\Serializer\Serializer(null, new \Jaddek\Serializer\Converter\NullToStringConverter());
$profile    = $serializer->denormalize($params, Profile::class);

//print_r($profile);
$reverted = $serializer->normalize($profile);
var_dump($reverted);
//
//if ($params == $reverted) {
//    echo "Array are equals\n";
//}