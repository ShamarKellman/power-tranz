<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Shamarkellman\PowerTranz\PowerTranz;

test('getEndpoint returns correct URL in test mode', function () {
    $mockHandler = new MockHandler();
    $handlerStack = HandlerStack::create($mockHandler);
    $client = new Client(['handler' => $handlerStack]);

    $powerTranz = new PowerTranz($client);
    $powerTranz->enableTestMode();
    expect($powerTranz->getEndpoint())->toBe(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
});

test('getEndpoint returns correct URL in production mode', function () {
    $mockHandler = new MockHandler();
    $handlerStack = HandlerStack::create($mockHandler);
    $client = new Client(['handler' => $handlerStack]);

    $powerTranz = new PowerTranz($client);
    $powerTranz->setTestMode(false);
    expect($powerTranz->getEndpoint())->toBe(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_PROD);
});

test('setTestMode correctly sets the test mode', function () {
    $mockHandler = new MockHandler();
    $handlerStack = HandlerStack::create($mockHandler);
    $client = new Client(['handler' => $handlerStack]);

    $powerTranz = new PowerTranz($client);
    $powerTranz->setTestMode(true);
    expect($powerTranz->getEndpoint())->toBe(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
    $powerTranz->setTestMode(false);
    expect($powerTranz->getEndpoint())->toBe(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_PROD);
});
