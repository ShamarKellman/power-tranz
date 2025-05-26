<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Support\Constants;

test('constants have expected values', function () {
    expect(Constants::DRIVER_NAME)->toBe('PowerTranz - Payment Gateway');
    expect(Constants::PLATFORM_PWT_UAT)->toBe('https://staging.ptranz.com/api/');
    expect(Constants::PLATFORM_PWT_PROD)->toBe('https://gateway.ptranz.com/api/');
    expect(Constants::CONFIG_KEY_PWTID)->toBe('PWTId');
    expect(Constants::CONFIG_KEY_PWTPWD)->toBe('PWTpwd');
    expect(Constants::CONFIG_KEY_MERCHANT_RESPONSE_URL)->toBe('merchantResponseURL');
});
