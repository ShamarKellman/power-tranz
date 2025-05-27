<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Responses\GenericResponse;

test('GenericResponse can be constructed and returns data', function () {
    $data = ['foo' => 'bar'];
    $response = new GenericResponse($data);

    /** @var stdClass $responseData */
    $responseData = $response->getData();
    expect($responseData)->toBeInstanceOf(stdClass::class)
        ->and($responseData->foo)->toBe('bar');
});
