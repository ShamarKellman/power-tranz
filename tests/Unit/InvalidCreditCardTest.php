<?php

use Shamarkellman\PowerTranz\Exceptions\InvalidCreditCard;

test('InvalidCreditCard can be thrown and caught', function () {
    try {
        throw new InvalidCreditCard('Invalid card');
    } catch (InvalidCreditCard $e) {
        expect($e->getMessage())->toBe('Invalid card');
    }
}); 