<?php

declare(strict_types=1);

use Shamarkellman\PowerTranz\Data\CardData;

test('CardData can be created with required fields', function () {
    $cardData = new CardData(
        number: '4242424242424242',
        expiryMonth: '12',
        expiryYear: '2025',
        cvv: '123',
    );

    expect($cardData->number)->toBe('4242424242424242')
        ->and($cardData->expiryMonth)->toBe('12')
        ->and($cardData->expiryYear)->toBe('2025')
        ->and($cardData->cvv)->toBe('123');
});

test('CardData can be created with optional fields', function () {
    $cardData = new CardData(
        number: '4242424242424242',
        expiryMonth: '12',
        expiryYear: '2025',
        cvv: '123',
        name: 'John Doe',
        firstName: 'John',
        lastName: 'Doe',
        address1: '123 Main St',
        address2: 'Apt 4B',
        city: 'New York',
        state: 'NY',
        postcode: '10001',
        country: 'US',
        email: 'john@example.com',
        phone: '1234567890',
    );

    expect($cardData->name)->toBe('John Doe')
        ->and($cardData->firstName)->toBe('John')
        ->and($cardData->lastName)->toBe('Doe')
        ->and($cardData->address1)->toBe('123 Main St')
        ->and($cardData->address2)->toBe('Apt 4B')
        ->and($cardData->city)->toBe('New York')
        ->and($cardData->state)->toBe('NY')
        ->and($cardData->postcode)->toBe('10001')
        ->and($cardData->country)->toBe('US')
        ->and($cardData->email)->toBe('john@example.com')
        ->and($cardData->phone)->toBe('1234567890');
});

test('CardData toArray returns correct structure', function () {
    $cardData = new CardData(
        number: '4242424242424242',
        expiryMonth: '12',
        expiryYear: '2025',
        cvv: '123',
        name: 'John Doe',
        firstName: 'John',
        lastName: 'Doe',
        address1: '123 Main St',
        address2: 'Apt 4B',
        city: 'New York',
        state: 'NY',
        postcode: '10001',
        country: 'US',
        email: 'john@example.com',
        phone: '1234567890',
    );

    $array = $cardData->toArray();

    expect($array)->toHaveKeys([
        'Number',
        'ExpiryMonth',
        'ExpiryYear',
        'Cvv',
        'Name',
        'FirstName',
        'LastName',
        'Address1',
        'Address2',
        'City',
        'State',
        'Postcode',
        'Country',
        'Email',
        'Phone',
    ]);
});
