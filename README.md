# PowerTranz

A PHP library for integrating with the PowerTranz payment gateway.

## Description

This library provides a robust interface to interact with the PowerTranz payment gateway, offering comprehensive payment processing capabilities including:

- Credit card processing
- 3DS (3D Secure) authentication
- Tokenization
- Hosted payment pages
- Transaction management (authorize, capture, void, refund)
- Fraud checking
- Address verification
- Support for both test and production environments

## Installation

You can install the library via Composer:

```bash
composer require shamarkellman/power-tranz
```

## Configuration

The library requires basic configuration to get started:

```php
use Shamarkellman\PowerTranz\PowerTranz;

$powerTranz = new PowerTranz();
$powerTranz->setPowerTranzId('your_id');
$powerTranz->setPowerTranzPassword('your_password');

// Optional configurations
$powerTranz->setTestMode(true); // Enable test mode
$powerTranz->set3DSMode(true); // Enable 3DS authentication
$powerTranz->setFraudCheckMode(true); // Enable fraud checking
$powerTranz->setIncludeBillingAddress(true); // Include billing address in requests
$powerTranz->setMerchantResponseURL('https://your-domain.com/callback'); // Set callback URL
```

## Usage Examples

### Basic Authorization

```php
use Shamarkellman\PowerTranz\Data\AuthorizationData;
use Shamarkellman\PowerTranz\Data\CardData;

$cardData = new CardData(
    number: '4242424242424242',
    expiryMonth: '12',
    expiryYear: '2025',
    cvv: '123',
    firstName: 'John',
    lastName: 'Doe'
);

$transactionData = new AuthorizationData(
    amount: 100.00,
    card: $cardData,
    currency: 'USD'
);

$response = $powerTranz->authorize($transactionData);

if ($response->isSuccessful()) {
    // Handle successful authorization
    $transactionNumber = $response->getTransactionNumber();
    $orderNumber = $response->getOrderNumber();
} else {
    // Handle errors
    $errors = $response->getErrorMessages();
}
```

### Tokenization

```php
$response = $powerTranz->tokenize($transactionData);
if ($response->isSuccessful()) {
    $token = $response->getSpiToken();
    // Store token for future transactions
}
```

### Hosted Payment Page

```php
$response = $powerTranz->getHostedPage(
    $transactionData,
    'Default', // PageSet
    'Default'  // PageName
);

if ($response->isRedirect()) {
    // Redirect to hosted payment page
    $redirectUrl = $response->redirect();
}
```

### Transaction Management

```php
// Capture a transaction
$captureData = new CaptureRefundData(
    amount: 100.00,
    transactionNumber: 'transaction_id'
);
$response = $powerTranz->capture($captureData);

// Void a transaction
$response = $powerTranz->void('transaction_id');

// Refund a transaction
$refundData = new CaptureRefundData(
    amount: 100.00,
    transactionNumber: 'transaction_id'
);
$response = $powerTranz->refund($refundData);
```

## Testing

Run the tests using PHPUnit:

```bash
./vendor/bin/phpunit
```

## License

This project is licensed under the terms of the license included in the LICENSE file.
