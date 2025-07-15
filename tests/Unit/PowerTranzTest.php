<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory;
use Shamarkellman\PowerTranz\PowerTranz;
use Shamarkellman\PowerTranz\Support\Constants;

test('getEndpoint returns correct URL in test mode', function () {
    /** @var \PHPUnit\Framework\MockObject\MockObject|Factory $factory */
    $factory = mock(Factory::class);

    $powerTranz = new PowerTranz($factory);
    expect($powerTranz->getEndpoint())->toBe(Constants::PLATFORM_PWT_UAT);
});

test('getEndpoint returns correct URL in production mode', function () {
    /** @var \PHPUnit\Framework\MockObject\MockObject|Factory $factory */
    $factory = mock(Factory::class);

    $powerTranz = new PowerTranz($factory);
    $powerTranz->setEndPoint(Constants::PLATFORM_PWT_PROD);
    expect($powerTranz->getEndpoint())->toBe(Constants::PLATFORM_PWT_PROD);
});
