<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Exceptions\InvalidResponseData;

test('InvalidResponseData can be thrown and caught', function () {
    try {
        throw new InvalidResponseData('Invalid response');
    } catch (InvalidResponseData $e) {
        expect($e->getMessage())->toBe('Invalid response');
    }
});
