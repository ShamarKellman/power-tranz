<?php

use Shamarkellman\PowerTranz\Exceptions\InvalidEmailAddress;

test('InvalidEmailAddress can be thrown and caught', function () {
    try {
        throw new InvalidEmailAddress('Invalid email');
    } catch (InvalidEmailAddress $e) {
        expect($e->getMessage())->toBe('Invalid email');
    }
}); 