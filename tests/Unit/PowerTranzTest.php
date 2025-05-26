<?php

use Shamarkellman\PowerTranz\PowerTranz;
use Shamarkellman\PowerTranz\Exceptions\InvalidCreditCard;

test('getEndpoint returns correct URL in test mode', function () {
    $powerTranz = new PowerTranz();
    $powerTranz->enableTestMode();
    expect($powerTranz->getEndpoint())->toBe(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
});

test('getEndpoint returns correct URL in production mode', function () {
    $powerTranz = new PowerTranz();
    $powerTranz->setTestMode(false);
    expect($powerTranz->getEndpoint())->toBe(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_PROD);
});

test('setTestMode correctly sets the test mode', function () {
    $powerTranz = new PowerTranz();
    $powerTranz->setTestMode(true);
    expect($powerTranz->getEndpoint())->toBe(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
    $powerTranz->setTestMode(false);
    expect($powerTranz->getEndpoint())->toBe(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_PROD);
});

test('validateCreditCard throws exception when credit card data is missing', function () {
    $powerTranz = new PowerTranz();
    expect(fn() => $powerTranz->authorize([]))->toThrow(InvalidCreditCard::class, 'Credit card data is required.');
});

test('validateCreditCard throws exception when required credit card fields are missing', function () {
    $powerTranz = new PowerTranz();
    $data = ['card' => []];
    expect(fn() => $powerTranz->authorize($data))->toThrow(InvalidCreditCard::class, 'Credit card number, expiry month, expiry year, and CVV are required.');
});
