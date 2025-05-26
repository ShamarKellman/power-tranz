<?php

use Shamarkellman\PowerTranz\Responses\PurchaseResponse;

test('PurchaseResponse can be constructed and returns data', function () {
    $data = ['foo' => 'bar'];
    $response = new PurchaseResponse($data);
    expect($response->getData())->toBeInstanceOf(stdClass::class);
    expect($response->getData()->foo)->toBe('bar');
});
