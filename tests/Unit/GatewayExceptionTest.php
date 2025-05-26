<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Exceptions\GatewayException;

test('GatewayException can be thrown and caught', function () {
    try {
        throw new GatewayException('Gateway error');
    } catch (GatewayException $e) {
        expect($e->getMessage())->toBe('Gateway error');
    }
});
