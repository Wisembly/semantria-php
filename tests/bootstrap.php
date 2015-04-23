<?php

use Guzzle\Tests\GuzzleTestCase;

include __DIR__ . '/../vendor/autoload.php';

// Mocks path
$mock_basepath = __DIR__ . '/Mock/';
GuzzleTestCase::setMockBasePath($mock_basepath);

// Service Builder for tests
Guzzle\Tests\GuzzleTestCase::setServiceBuilder(
    Guzzle\Service\Builder\ServiceBuilder::factory(
        [
            'semantria.auth' => [
                'class' => 'Semantria.SemantriaAuthClient',
                'params' => [
                    'consumer_key' => 'foo',
                    'consumer_secret' => 'bar'
                ]
            ]
        ]
    )
);
