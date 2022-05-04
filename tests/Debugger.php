<?php

namespace tests;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final class Debugger
{
    public static function debug(mixed $value)
    {
        $encoders = [new JsonEncoder(new JsonEncode([JsonEncode::OPTIONS => JSON_PRETTY_PRINT]))];
        $normalizers = [new PropertyNormalizer(), new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
        codecept_debug($serializer->serialize($value, 'json'));
    }
}
