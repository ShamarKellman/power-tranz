# PowerTranz

A PHP library for integrating with the PowerTranz payment gateway.

## Description

This library provides a simple interface to interact with the PowerTranz payment gateway, allowing you to process payments, handle 3DS authentication, and manage transactions.

## Installation

You can install the library via Composer:

```bash
composer require shamarkellman/power_tranz
```

## Usage

```php
use Shamarkellman\PowerTranz\PowerTranz;

$powerTranz = new PowerTranz();
$powerTranz->setPowerTranzId('your_id');
$powerTranz->setPowerTranzPassword('your_password');

// Example: Authorize a transaction
$response = $powerTranz->authorize([
    'amount' => 100,
    'currency' => 'USD',
    // other transaction data
]);
```

## Testing

Run the tests using PHPUnit:

```bash
./vendor/bin/phpunit
```

## License

This project is licensed under the terms of the license included in the LICENSE file.
