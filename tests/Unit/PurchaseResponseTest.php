<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Responses\PurchaseResponse;

test('PurchaseResponse can be constructed and returns data', function () {
    $data = ['foo' => 'bar'];
    $response = new PurchaseResponse($data);

    $object = $response->getData();

    expect($object)->toBeInstanceOf(stdClass::class);
    /** @phpstan-ignore-next-line  */
    expect($object->foo)->toBe('bar');
});
