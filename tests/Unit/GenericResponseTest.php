<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Responses\GenericResponse;

test('GenericResponse can be constructed and returns data', function () {
    $data = ['foo' => 'bar'];
    $response = new GenericResponse($data);
    expect($response->getData())->toBeInstanceOf(stdClass::class);
    expect($response->getData()->foo)->toBe('bar');
});
