<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Exceptions\RequiredFieldEmpty;

test('RequiredFieldEmpty can be thrown and caught', function () {
    try {
        throw new RequiredFieldEmpty('Field is required');
    } catch (RequiredFieldEmpty $e) {
        expect($e->getMessage())->toBe('Field is required');
    }
});
