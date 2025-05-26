<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Support\CreditCard;

test('mask returns masked credit card number', function () {
    expect(CreditCard::mask('4111111111111111'))->toBe('4XXXXXXXXXXX1111');
});

test('format returns formatted credit card number', function () {
    expect(CreditCard::format('4111111111111111'))->toBe('4111-1111-1111-1111');
});

test('number returns only digits from credit card number', function () {
    expect(CreditCard::number('4111-1111-1111-1111'))->toBe('4111111111111111');
});
