<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Data\AuthorizationData;
use Shamarkellman\PowerTranz\Data\CardData;

test('AuthorizationData can be created with required fields', function () {
    $cardData = new CardData(
        number: '4242424242424242',
        expiryMonth: '12',
        expiryYear: '2025',
        cvv: '123',
    );

    $authData = new AuthorizationData(
        amount: 100.00,
        card: $cardData,
    );

    expect($authData->amount)->toBe(100.00)
        ->and($authData->card)->toBeInstanceOf(CardData::class)
        ->and($authData->currency)->toBeNull()
        ->and($authData->addressMatch)->toBeFalse()
        ->and($authData->transactionNumber)->toBeNull()
        ->and($authData->orderNumber)->toBeNull();
});

test('AuthorizationData can be created with optional fields', function () {
    $cardData = new CardData(
        number: '4242424242424242',
        expiryMonth: '12',
        expiryYear: '2025',
        cvv: '123',
        firstName: 'John',
        lastName: 'Doe',
    );

    $authData = new AuthorizationData(
        amount: 100.00,
        card: $cardData,
        currency: 'USD',
        addressMatch: true,
        transactionNumber: '123456',
        orderNumber: 'ORDER-123',
    );

    expect($authData->amount)->toBe(100.00)
        ->and($authData->card)->toBeInstanceOf(CardData::class)
        ->and($authData->currency)->toBe('USD')
        ->and($authData->addressMatch)->toBeTrue()
        ->and($authData->transactionNumber)->toBe('123456')
        ->and($authData->orderNumber)->toBe('ORDER-123');
});

test('AuthorizationData toArray returns correct structure', function () {
    $cardData = new CardData(
        number: '4242424242424242',
        expiryMonth: '12',
        expiryYear: '2025',
        cvv: '123',
        firstName: 'John',
        lastName: 'Doe',
    );

    $authData = new AuthorizationData(
        amount: 100.00,
        card: $cardData,
        currency: 'USD',
        addressMatch: true,
        transactionNumber: '123456',
        orderNumber: 'ORDER-123',
    );

    $array = $authData->toArray();

    expect($array)->toHaveKeys([
        'amount',
        'currency',
        'AddressMatch',
        'transactionNumber',
        'orderNumber',
        'card',
    ])
    ->and($array['card'])->toHaveKeys([
        'Number',
        'ExpiryMonth',
        'ExpiryYear',
        'Cvv',
        'FirstName',
        'LastName',
    ]);
});
