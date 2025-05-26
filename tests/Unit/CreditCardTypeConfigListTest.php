<?php

use Shamarkellman\PowerTranz\Support\CreditCardTypeConfigList;
use Shamarkellman\PowerTranz\Support\CreditCardValidator;

test('get returns array with Visa config', function () {
    $configs = CreditCardTypeConfigList::get();
    expect($configs)->toBeArray();
    expect($configs)->toHaveKey(CreditCardValidator::TYPE_VISA);
    expect($configs[CreditCardValidator::TYPE_VISA]['niceType'])->toBe('Visa');
}); 