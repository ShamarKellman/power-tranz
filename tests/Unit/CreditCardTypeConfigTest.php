<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Support\CreditCardTypeConfig;

test('can construct and get properties', function () {
    $config = [
        'niceType' => 'Visa',
        'type' => 'visa',
        'patterns' => [4],
        'gaps' => [4, 8, 12],
        'lengths' => [16],
        'code' => ['name' => 'CVV', 'size' => 3],
        'luhnCheck' => true,
    ];
    $ccType = new CreditCardTypeConfig($config);
    expect($ccType->getNiceType())->toBe('Visa');
    expect($ccType->getType())->toBe('visa');
    expect($ccType->getPatterns())->toBe([4]);
    expect($ccType->getGaps())->toBe([4, 8, 12]);
    expect($ccType->getLengths())->toBe([16]);
    expect($ccType->getCode())->toBe(['name' => 'CVV', 'size' => 3]);
    expect($ccType->getLuhnCheck())->toBeTrue();
});

test('matches returns true for valid card number', function () {
    $config = [
        'niceType' => 'Visa',
        'type' => 'visa',
        'patterns' => [4],
        'gaps' => [4, 8, 12],
        'lengths' => [16],
        'code' => ['name' => 'CVV', 'size' => 3],
        'luhnCheck' => true,
    ];
    $ccType = new CreditCardTypeConfig($config);
    expect($ccType->matches('4111111111111111'))->toBeTrue();
});
