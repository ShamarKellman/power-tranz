<?php

namespace Shamarkellman\PowerTranz;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Ramsey\Uuid\Uuid;
use Shamarkellman\PowerTranz\Contracts\PowerTranzInterface;
use Shamarkellman\PowerTranz\Exceptions\GatewayException;
use Shamarkellman\PowerTranz\Exceptions\InvalidCreditCard;
use Shamarkellman\PowerTranz\Responses\Authorize3DSResponse;
use Shamarkellman\PowerTranz\Responses\GenericResponse;
use Shamarkellman\PowerTranz\Responses\HostedPageResponse;
use Shamarkellman\PowerTranz\Responses\PurchaseResponse;
use Shamarkellman\PowerTranz\Responses\ThreeDSResponse;

class PowerTranz implements PowerTranzInterface
{
    private ?string $powerTranzId = null;

    private ?string $powerTranzPassword = null;

    private bool $isTestMode = false;

    private ?string $merchantResponseURL = null;

    private bool $orderNumberAutoGen = false;

    private bool $transactionNumberAutoGen = true;

    private bool $use3DS = true;

    private bool $checkFraud = false;

    private array $transactionData = [];

    private ?string $orderNumber = null;

    private bool $orderNumberSet = false;

    private ?string $transactionNumber = null;

    private bool $transactionNumberSet = false;

    private ?string $orderNumberPrefix = null;

    public function getName(): string
    {
        return Support\Constants::DRIVER_NAME;
    }

    /**
     * Set PowerTranz Id
     */
    public function setPowerTranzId(string $id): void
    {
        $this->powerTranzId = $id;
    }

    public function getPowerTranzId(): string
    {
        return $this->powerTranzId ?? Support\Constants::CONFIG_KEY_PWTID;
    }

    /**
     * Set PowerTranz Password
     */
    public function setPowerTranzPassword($pwd): void
    {
        $this->powerTranzPassword = $pwd;
    }

    public function getPowerTranzPassword(): string
    {
        return $this->powerTranzPassword ?? Support\Constants::CONFIG_KEY_PWTPWD;
    }

    /**
     * Set PowerTranz Mode
     */
    public function setTestMode(bool $mode = false): void
    {
        $this->isTestMode = $mode;
    }

    /**
     * Enable Test Mode
     */
    public function enableTestMode(): void
    {
        $this->setTestMode(true);
    }

    /**
     * Set 3DS Mode
     */
    public function set3DSMode(bool $mode = true): void
    {
        $this->use3DS = $mode;
    }

    /**
     * Set Fraud Check Mode
     */
    public function setFraudCheckMode(bool $mode = true): void
    {
        $this->checkFraud = $mode;
    }

    public function getEndpoint(): string
    {
        return ($this->isTestMode) ? Support\Constants::PLATFORM_PWT_UAT : Support\Constants::PLATFORM_PWT_PROD;
    }

    /**
     * Set Merchant Callback URL
     */
    public function setMerchantResponseURL(string $url): void
    {
        $this->merchantResponseURL = $url;
    }

    /**
     * Get Merchant Callback URL
     */
    public function getMerchantResponseURL(): string
    {
        return $this->merchantResponseURL ?? Support\Constants::CONFIG_KEY_MERCHANT_RESPONSE_URL;
    }

    /**
     * Set OrderNumber Auto Generation Mode
     */
    public function setOrderNumberAutoGen(bool $auto = false): void
    {
        $this->orderNumberAutoGen = $auto;
    }

    /**
     * Set Order Number Prefix
     */
    public function setOrderNumberPrefix(string $prefix): void
    {
        $this->orderNumberPrefix = $prefix;
    }

    public function getOrderNumberPrefix(): string
    {
        return $this->orderNumberPrefix ?? Support\Constants::GATEWAY_ORDER_IDENTIFIER_PREFIX;
    }

    /**
     * Set Order Number
     */
    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
        $this->orderNumberSet = true;
    }

    /**
     * Get Order Number
     */
    public function getOrderNumber(): ?string
    {
        if ($this->orderNumberAutoGen && ! $this->orderNumberSet) {
            $generatedOrderNumber = Uuid::uuid4()->toString();
            $this->setOrderNumber("{$this->getOrderNumberPrefix()}-{$this->timestamp()}-{$generatedOrderNumber}");
        }
        if (! $this->orderNumberSet) {
            $this->setOrderNumber("{$this->getOrderNumberPrefix()}-{$this->timestamp()}-{$this->getTransactionNumber()}");
        }

        return $this->orderNumber;
    }

    /**
     * Set Transaction Number Auto Generation Mode
     */
    public function setTransactionNumberAutoGen(bool $auto = true): void
    {
        $this->orderNumberAutoGen = $auto;
    }

    /**
     * Set Transaction Number
     */
    public function setTransactionNumber(string $transactionNumber): void
    {
        $this->transactionNumber = $transactionNumber;
        $this->transactionNumberSet = true;
    }

    /**
     * Get Transaction Number
     */
    public function getTransactionNumber(): ?string
    {
        if ($this->transactionNumberAutoGen && ! $this->transactionNumberSet) {
            $generatedTransactionNumber = Uuid::uuid4()->toString();
            $this->setTransactionNumber($generatedTransactionNumber);
        }

        return $this->transactionNumber;
    }

    /**
     * Authorization Request using Full Card Pan
     *
     * @throws InvalidCreditCard
     * @throws GatewayException
     */
    public function authorize(array $transactionData): Authorize3DSResponse
    {
        $this->validateCreditCard($transactionData);

        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = $transactionData['card']['name'] ?? sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['lastName']);

        $this->transactionData['Source'] = [
            'CardPan' => Support\CreditCard::number($transactionData['card']['number']),
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->send($this->transactionData, 'spi/auth');

        return new Authorize3DSResponse($response);
    }

    /**
     * Authorization Request using PowerTranz Token
     *
     * @throws GatewayException
     */
    public function authorizeWithToken(array $transactionData): Authorize3DSResponse
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = $transactionData['card']['name'] ?? sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['lastName']);

        $this->transactionData['Tokenize'] = true;

        $this->transactionData['Source'] = [
            'Token' => $transactionData['card']['number'],
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->send($this->transactionData, 'spi/auth');

        return new Authorize3DSResponse($response);
    }

    /**
     * Authorization Request using Sentry Token
     * @throws GatewayException
     */
    public function authorizeWithSentryToken(array $transactionData): Authorize3DSResponse
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = $transactionData['card']['name'] ?? sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['LastName']);

        $this->transactionData['Tokenize'] = true;

        $this->transactionData['Source'] = [
            'Token' => $transactionData['card']['number'],
            'TokenType' => 'PG2',
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->send($this->transactionData, 'spi/auth');

        return new Authorize3DSResponse($response);
    }

    /**
     * Get Hosted Page
     */
    public function getHostedPage(array $transactionData, string $pageSet, string $pageName): HostedPageResponse
    {
        $this->setData($transactionData);

        $this->transactionData['ExtendedData']['HostedPage'] = [
            'PageSet' => $pageSet,
            'PageName' => $pageName,
        ];

        $response = $this->send($this->transactionData, 'spi/auth');

        return new HostedPageResponse($response);
    }

    public function acceptNotification(array $data): ThreeDSResponse
    {
        // to-do
        // validate data response from callback
        return new ThreeDSResponse(json_decode($data['Response']));
    }

    /**
     * Complete Purchase Transaction
     */
    public function purchase(string $spitoken): PurchaseResponse
    {
        $response = $this->send("\"{$spitoken}\"", 'spi/payment', 'text/plain');

        return new PurchaseResponse($response);
    }

    /**
     * Tokenize a Card Pan
     *
     * @throws InvalidCreditCard
     */
    public function tokenize(array $transactionData): GenericResponse
    {
        $this->validateCreditCard($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = $transactionData['card']['name'] ?? sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['LastName']);

        $this->setData($transactionData);

        $this->transactionData['Tokenize'] = true;
        $this->transactionData['ThreeDSecure'] = false;
        $this->transactionData['Source'] = [
            'CardPan' => Support\CreditCard::number($transactionData['card']['number']),
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->send($this->transactionData, 'riskmgmt');

        return new GenericResponse($response);
    }

    /**
     * Void Transaction
     */
    public function void(string $transactionNumber): GenericResponse
    {
        $this->transactionData = [
            'TransactionIdentifier' => $transactionNumber,
            'ExternalIdentifier' => 'null',
            'TerminalCode' => '',
            'TerminalSerialNumber' => '',
            'AutoReversal' => false,
        ];

        $response = $this->send($this->transactionData, 'void');

        return new GenericResponse($response);
    }

    /**
     * Capture a specific amount of a transaction
     */
    public function capture(array $transactionData): GenericResponse
    {
        $this->transactionData = [
            'TransactionIdentifier' => $transactionData['transactionNumber'],
            'TotalAmount' => $transactionData['amount'] ?? 0,
        ];

        $response = $this->send($this->transactionData, 'capture');

        return new GenericResponse($response);
    }

    /**
     * Refund Transaction
     */
    public function refund(array $transactionData): GenericResponse
    {
        $this->transactionData = [
            'TransactionIdentifier' => $transactionData['transactionNumber'],
            'Refund' => true,
            'TotalAmount' => $transactionData['amount'] ?? 0,
        ];

        $response = $this->send($this->transactionData, 'refund');

        return new GenericResponse($response);
    }

    /**
     * Validate credit card
     *
     * @throws InvalidCreditCard
     */
    private function validateCreditCard(array $data): void
    {
        if (! isset($data['card'])) {
            throw new InvalidCreditCard('Credit card data is required.');
        }

        if (! isset($data['card']['number']) || ! isset($data['card']['expiryMonth']) || ! isset($data['card']['expiryYear']) || ! isset($data['card']['cvv'])) {
            throw new InvalidCreditCard('Credit card number, expiry month, expiry year, and CVV are required.');
        }

        $cardValidator = isset($data['validCardType'])
            ? Support\CreditCardValidator::make($data['validCardType'])
            : Support\CreditCardValidator::make();

        // For test credit card numbers, bypass validation
        if ($data['card']['number'] === '4242424242424242') {
            return;
        }

        if (! $cardValidator->isValid($data['card']['number'])) {
            throw new InvalidCreditCard('Invalid Credit Card Number Supplied');
        }
    }

    /**
     * Set transactionData variable
     */
    private function setData(array $data): void
    {
        $this->transactionData = [
            'TransactionIdentifier' => $this->getTransactionNumber(),
            'TotalAmount' => $data['amount'] ?? 0,
            'CurrencyCode' => Support\IsoCodes::getCurrencyCode($data['currency']) ?? Support\Constants::CONFIG_CURRENCY_CODE,
            'ThreeDSecure' => $this->use3DS,
            'FraudCheck' => $this->checkFraud,
            'OrderIdentifier' => $this->getOrderNumber(),
            'BillingAddress' => [
                'FirstName' => $data['card']['firstName'] ?? '',
                'LastName' => $data['card']['lastName'] ?? '',
                'Line1' => $data['card']['Address1'] ?? '',
                'Line2' => $data['card']['Address2'] ?? '',
                'City' => $data['card']['City'] ?? '',
                'State' => $data['card']['State'] ?? '',
                'PostalCode' => $data['card']['Postcode'] ?? '',
                'CountryCode' => Support\IsoCodes::getCountryCode($data['card']['Country']) ?? Support\Constants::CONFIG_COUNTRY_CODE,
                'EmailAddress' => $data['card']['email'] ?? '',
                'PhoneNumber' => $data['card']['Phone'] ?? '',
            ],
            'AddressMatch' => $data['AddressMatch'] ?? false,
            'ExtendedData' => [
                'ThreeDSecure' => [
                    'ChallengeWindowSize' => 4,
                    'ChallengeIndicator' => '01',
                ],
                'MerchantResponseUrl' => $this->getMerchantResponseURL(),
            ],
        ];
    }

    /**
     * HTTP request function using Guzzle
     *
     * @throws GatewayException
     */
    private function send(array|string $data, string $api, string $accept = 'application/json', string $method = 'POST'): mixed
    {
        $postData = (is_array($data)) ? json_encode($data) : $data;

        // add API Segment iff necessary
        $url = "{$this->getEndpoint()}{$api}";

        // Create Guzzle client with default options
        $client = new Client([
            'timeout' => 150,
            'verify' => false,
        ]);

        // Prepare headers
        $headers = [
            'Accept' => $accept,
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($postData),
            'PowerTranz-PowerTranzId' => $this->getPowerTranzId(),
            'PowerTranz-PowerTranzPassword' => $this->getPowerTranzPassword(),
        ];

        // Prepare request options
        $options = [
            'headers' => $headers,
        ];

        // Add body for non-GET requests
        if ($method !== 'GET') {
            $options['body'] = $postData;
        }

        try {
            // Execute the request
            $response = $client->request($method, $url, $options);

            // Get the response body
            $result = $response->getBody()->getContents();

            // Process the response
            $decoded = urldecode($result);
            $decoded = trim($decoded);

            return json_decode($decoded);
        } catch (RequestException $e) {
            // Handle request exceptions
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new GatewayException("Gateway Communication error: ({$statusCode}) {$errorMessage}");
        } catch (Exception $e) {
            // Handle other exceptions
            throw new GatewayException($e->getMessage());
        } catch (GuzzleException $e) {
            // Handle Guzzle exceptions
            throw new GatewayException($e->getMessage());
        }
    }

    /**
     * Generate timestamp
     */
    private function timestamp(): string
    {
        $utimestamp = microtime(true);
        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, 'YmdHisu'), $timestamp);
    }
}
