<?php

use Shamarkellman\PowerTranz\Support\IsoCodes;

test('getCountryCode returns correct code for country name', function () {
    expect(IsoCodes::getCountryCode('Jamaica'))->toBe('388');
});

test('getCountryCode returns correct code for alpha-2', function () {
    expect(IsoCodes::getCountryCode('JM'))->toBe('388');
});

test('getCurrencyCode returns correct code for currency name', function () {
    expect(IsoCodes::getCurrencyCode('JMD'))->toBe('388');
}); 