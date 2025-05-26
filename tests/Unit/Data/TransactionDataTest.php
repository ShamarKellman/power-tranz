<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Data\TransactionData;

test('TransactionData can be created with required fields', function () {
    $transactionData = new TransactionData(
        amount: 100.00,
    );

    expect($transactionData->amount)->toBe(100.00)
        ->and($transactionData->currency)->toBeNull()
        ->and($transactionData->addressMatch)->toBeFalse()
        ->and($transactionData->transactionNumber)->toBeNull()
        ->and($transactionData->orderNumber)->toBeNull();
});

test('TransactionData can be created with optional fields', function () {
    $transactionData = new TransactionData(
        amount: 100.00,
        currency: 'USD',
        addressMatch: true,
        transactionNumber: '123456',
        orderNumber: 'ORDER-123',
    );

    expect($transactionData->amount)->toBe(100.00)
        ->and($transactionData->currency)->toBe('USD')
        ->and($transactionData->addressMatch)->toBeTrue()
        ->and($transactionData->transactionNumber)->toBe('123456')
        ->and($transactionData->orderNumber)->toBe('ORDER-123');
});

test('TransactionData toArray returns correct structure', function () {
    $transactionData = new TransactionData(
        amount: 100.00,
        currency: 'USD',
        addressMatch: true,
        transactionNumber: '123456',
        orderNumber: 'ORDER-123',
    );

    $array = $transactionData->toArray();

    expect($array)->toHaveKeys([
        'amount',
        'currency',
        'AddressMatch',
        'transactionNumber',
        'orderNumber',
    ]);
});
