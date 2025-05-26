<?php

use Shamarkellman\PowerTranz\PowerTranz;
use Shamarkellman\PowerTranz\Responses\Authorize3DSResponse;

test('authorize method returns expected response with valid credit card data', function () {
    $powerTranz = new PowerTranz;
    $powerTranz->setPowerTranzId('test_id');
    $powerTranz->setPowerTranzPassword('test_password');
    $powerTranz->enableTestMode();

    $response = $powerTranz->authorize([
        'card' => [
            'number' => '4242424242424242',
            'expiryMonth' => '12',
            'expiryYear' => '2025',
            'cvv' => '123',
            'Country' => 'Jamaica',
        ],
        'amount' => 100,
        'currency' => 'USD',
        'orderNumber' => 'test_order_123',
    ]);

    expect($response)->toBeInstanceOf(Authorize3DSResponse::class);
});
