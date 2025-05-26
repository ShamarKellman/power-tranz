<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Data\CaptureRefundData;

test('CaptureRefundData can be created with required fields', function () {
    $data = new CaptureRefundData(
        amount: 100.00,
        transactionNumber: '123456',
    );

    expect($data->amount)->toBe(100.00)
        ->and($data->transactionNumber)->toBe('123456')
        ->and($data->currency)->toBeNull();
});

test('CaptureRefundData can be created with optional fields', function () {
    $data = new CaptureRefundData(
        amount: 100.00,
        transactionNumber: '123456',
        currency: 'USD',
    );

    expect($data->amount)->toBe(100.00)
        ->and($data->transactionNumber)->toBe('123456')
        ->and($data->currency)->toBe('USD');
});

test('CaptureRefundData toArray returns correct structure', function () {
    $data = new CaptureRefundData(
        amount: 100.00,
        transactionNumber: '123456',
        currency: 'USD',
    );

    $array = $data->toArray();

    expect($array)->toHaveKeys([
        'amount',
        'currency',
        'transactionNumber',
    ]);
});
