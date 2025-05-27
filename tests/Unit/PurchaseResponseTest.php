<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Responses\PurchaseResponse;

test('PurchaseResponse can be constructed and returns data', function () {
    $data = ['foo' => 'bar'];
    $response = new PurchaseResponse($data);

    /** @var stdClass $object */
    $object = $response->getData();
    expect()->toBeInstanceOf(stdClass::class);
    expect($object->foo)->toBe('bar');
});
