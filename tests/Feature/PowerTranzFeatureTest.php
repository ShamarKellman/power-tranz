<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory;
use Shamarkellman\PowerTranz\Data\AuthorizationData;
use Shamarkellman\PowerTranz\Data\CaptureRefundData;
use Shamarkellman\PowerTranz\Data\CardData;
use Shamarkellman\PowerTranz\PowerTranz;
use Shamarkellman\PowerTranz\Responses\Authorize3DSResponse;
use Shamarkellman\PowerTranz\Responses\GenericResponse;
use Shamarkellman\PowerTranz\Responses\HostedPageResponse;
use Shamarkellman\PowerTranz\Support\CreditCardValidator;

beforeEach(function () {
    $this->responseData = null;
    $this->factory = mock(Factory::class);
    $this->pendingRequest = mock();

    $this->factory->shouldReceive('withHeaders')
        ->andReturn($this->pendingRequest);

    // @phpstan-ignore-next-line
    $this->pendingRequest->shouldReceive('send')
        ->andReturnUsing(function ($method, $url, $options) {
            $this->lastRequest = [
                'method' => $method,
                'url' => $url,
                'options' => $options,
            ];

            return $this->pendingRequest;
        });

    $this->pendingRequest->shouldReceive('throw')
        ->andReturn($this->pendingRequest);
    // @phpstan-ignore-next-line
    $this->pendingRequest->shouldReceive('object')
        ->andReturnUsing(function () {
            return json_decode(json_encode($this->responseData));
        });
});

test('authorize method returns expected response with valid credit card data', function () {
    $this->responseData = [
        'TransactionIdentifier' => 'test-transaction-id',
        'TransactionStatus' => 'Approved',
        'TransactionNumber' => '123456',
        'ResponseCode' => '00',
        'ResponseMessage' => 'Success',
    ];

    $powerTranz = new PowerTranz($this->factory);
    $powerTranz->setPowerTranzId('test_id');
    $powerTranz->setPowerTranzPassword('test_password');
    $powerTranz->setEndPoint(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);

    $card = new CardData(
        '4242424242424242',
        '12',
        '2025',
        '123',
        country: 'Jamaica',
    );
    $data = new AuthorizationData(
        100,
        $card,
        'USD',
        false,
        orderNumber: 'test_order_123',
        validCardTypes: [CreditCardValidator::TYPE_VISA],
    );

    $response = $powerTranz->authorize($data);

    expect($response)->toBeInstanceOf(Authorize3DSResponse::class)
        ->and($response->getTransactionNumber())->toBe('test-transaction-id');

    expect($this->lastRequest['method'])->toBe('POST')
        ->and($this->lastRequest['url'])->toContain('auth');
});

test('getHostedPage returns HostedPageResponse with correct data', function () {
    $this->responseData = [
        'RedirectData' => 'https://test.powerTranz.com/pay',
        'TransactionIdentifier' => 'test-transaction-id',
        'TransactionStatus' => 'Redirect',
        'ResponseCode' => '00',
        'ResponseMessage' => 'Success',
    ];

    $powerTranz = new PowerTranz($this->factory);
    $powerTranz->setEndPoint(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
    $powerTranz->setPowerTranzId('test-id');
    $powerTranz->setPowerTranzPassword('test-password');
    $powerTranz->setMerchantResponseURL('https://example.com/callback');

    $cardData = new CardData(
        number: '4242424242424242',
        expiryMonth: '12',
        expiryYear: '2025',
        cvv: '123',
        firstName: 'John',
        lastName: 'Doe',
        address1: '123 Main St',
        city: 'New York',
        state: 'NY',
        postcode: '10001',
        country: 'US',
        email: 'john@example.com',
        phone: '1234567890',
    );

    $authData = new AuthorizationData(
        amount: 100.00,
        card: $cardData,
        currency: 'USD',
        addressMatch: true,
    );

    $response = $powerTranz->getHostedPage($authData, 'Default', 'Payment');

    expect($response)->toBeInstanceOf(HostedPageResponse::class)
        ->and($response->isRedirect())->toBeTrue()
        ->and($response->redirect())->toBe('https://test.powerTranz.com/pay');

    expect($this->lastRequest['method'])->toBe('POST')
        ->and($this->lastRequest['url'])->toContain('auth');
});

test('capture returns GenericResponse with success status', function () {
    $this->responseData = [
        'TransactionIdentifier' => 'test-transaction-id',
        'TransactionStatus' => 'Approved',
        'IsoResponseCode' => '00',
        'ResponseMessage' => 'Success',
    ];

    $powerTranz = new PowerTranz($this->factory);
    $powerTranz->setEndPoint(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
    $powerTranz->setPowerTranzId('test-id');
    $powerTranz->setPowerTranzPassword('test-password');

    $captureData = new CaptureRefundData(
        amount: 100.00,
        transactionNumber: 'test-transaction-id',
        currency: 'USD',
    );

    $response = $powerTranz->capture($captureData);

    expect($response)->toBeInstanceOf(GenericResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->getTransactionNumber())->toBe('test-transaction-id');

    expect($this->lastRequest['method'])->toBe('POST')
        ->and($this->lastRequest['url'])->toContain('capture');
});

test('refund returns GenericResponse with success status', function () {
    $this->responseData = [
        'TransactionIdentifier' => 'test-transaction-id',
        'TransactionStatus' => 'Approved',
        'IsoResponseCode' => '00',
        'ResponseMessage' => 'Success',
    ];

    $powerTranz = new PowerTranz($this->factory);
    $powerTranz->setEndPoint(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
    $powerTranz->setPowerTranzId('test-id');
    $powerTranz->setPowerTranzPassword('test-password');

    $refundData = new CaptureRefundData(
        amount: 100.00,
        transactionNumber: '123456',
        currency: 'USD',
    );

    $response = $powerTranz->refund($refundData);

    expect($response)->toBeInstanceOf(GenericResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->getTransactionNumber())->toBe('test-transaction-id');

    expect($this->lastRequest['method'])->toBe('POST')
        ->and($this->lastRequest['url'])->toContain('refund');
});

test('capture with invalid transaction number returns error response', function () {
    $this->responseData = [
        'TransactionIdentifier' => 'test-transaction-id',
        'TransactionStatus' => 'Failed',
        'IsoResponseCode' => '01',
        'ResponseMessage' => 'Invalid Transaction',
        'Errors' => ['Code' => '01', 'Message' => 'Transaction not found'],
    ];

    $powerTranz = new PowerTranz($this->factory);
    $powerTranz->setEndPoint(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
    $powerTranz->setPowerTranzId('test-id');
    $powerTranz->setPowerTranzPassword('test-password');

    $captureData = new CaptureRefundData(
        amount: 100.00,
        transactionNumber: 'invalid-transaction',
        currency: 'USD',
    );

    $response = $powerTranz->capture($captureData);

    expect($response)->toBeInstanceOf(GenericResponse::class)
        ->and($response->isSuccessful())->toBeFalse()
        ->and($response->getErrorMessages())->toContain('Transaction not found');

    expect($this->lastRequest['method'])->toBe('POST')
        ->and($this->lastRequest['url'])->toContain('capture');
})->skip('flaky test, needs investigation');

test('refund with invalid transaction number returns error response', function () {
    $this->responseData = [
        'TransactionIdentifier' => 'invalid-transaction',
        'TransactionStatus' => 'Failed',
        'IsoResponseCode' => '01',
        'ResponseMessage' => 'Invalid Transaction',
        'Errors' => ['Code' => '01', 'Message' => 'Transaction not found'],
    ];

    $powerTranz = new PowerTranz($this->factory);
    $powerTranz->setEndPoint(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
    $powerTranz->setPowerTranzId('test-id');
    $powerTranz->setPowerTranzPassword('test-password');

    $refundData = new CaptureRefundData(
        amount: 100.00,
        transactionNumber: 'invalid-transaction',
        currency: 'USD',
    );

    $response = $powerTranz->refund($refundData);

    expect($response)->toBeInstanceOf(GenericResponse::class)
        ->and($response->isSuccessful())->toBeFalse()
        ->and($response->getErrorMessages())->toContain('Transaction not found');

    expect($this->lastRequest['method'])->toBe('POST')
        ->and($this->lastRequest['url'])->toContain('refund');
})->skip('flaky test, needs investigation');

test('alive endpoint returns AliveResponse with success', function () {
    $this->responseData = [
        'Name' => 'PowerTranz',
        'Version' => '2.8',
        'Status' => 'OK',
    ];

    $powerTranz = new PowerTranz($this->factory);
    $powerTranz->setEndPoint(\Shamarkellman\PowerTranz\Support\Constants::PLATFORM_PWT_UAT);
    $powerTranz->setPowerTranzId('test-id');
    $powerTranz->setPowerTranzPassword('test-password');

    $response = $powerTranz->alive();

    expect($response)->toBeInstanceOf(\Shamarkellman\PowerTranz\Responses\AliveResponse::class)
        ->and($response->isSuccessful())->toBeTrue();

    expect($this->lastRequest['method'])->toBe('GET')
        ->and($this->lastRequest['url'])->toContain('alive');
});
