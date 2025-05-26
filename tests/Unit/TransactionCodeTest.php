<?php

use Shamarkellman\PowerTranz\Support\TransactionCode;

test('can add and retrieve codes', function () {
    $tc = new TransactionCode([TransactionCode::AVS_CHECK, TransactionCode::FRAUD_TEST]);
    expect($tc->getUserCodes())->toContain(TransactionCode::AVS_CHECK);
    expect($tc->getUserCodes())->toContain(TransactionCode::FRAUD_TEST);
    expect($tc->hasCode(TransactionCode::AVS_CHECK))->toBeTrue();
    expect($tc->getCode())->toBe((string)(TransactionCode::AVS_CHECK + TransactionCode::FRAUD_TEST));
}); 