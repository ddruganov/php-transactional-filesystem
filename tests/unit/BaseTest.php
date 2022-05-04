<?php

namespace tests\unit;

use Codeception\Test\Unit;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class BaseTest extends Unit
{
    protected function dump(mixed $obj)
    {
        $encoders = [new JsonEncoder(new JsonEncode([JsonEncode::OPTIONS => JSON_PRETTY_PRINT]))];
        $normalizers = [new PropertyNormalizer(), new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
        codecept_debug($serializer->serialize($obj, 'json'));
    }
}
