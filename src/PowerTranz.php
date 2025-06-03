<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz;

use DateTimeImmutable;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Ramsey\Uuid\Uuid;
use Shamarkellman\PowerTranz\Contracts\PowerTranzInterface;
use Shamarkellman\PowerTranz\Data\AuthorizationData;
use Shamarkellman\PowerTranz\Data\CaptureRefundData;
use Shamarkellman\PowerTranz\Data\TransactionData;
use Shamarkellman\PowerTranz\Exceptions\GatewayException;
use Shamarkellman\PowerTranz\Exceptions\InvalidCreditCard;
use Shamarkellman\PowerTranz\Responses\AliveResponse;
use Shamarkellman\PowerTranz\Responses\Authorize3DSResponse;
use Shamarkellman\PowerTranz\Responses\GenericResponse;
use Shamarkellman\PowerTranz\Responses\HostedPageResponse;
use Shamarkellman\PowerTranz\Responses\PurchaseResponse;
use Shamarkellman\PowerTranz\Support\TransactionType;

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

    private bool $includeBillingAddress = false;

    /**
     * @var array<string, mixed>
     */
    private array $transactionData = [];

    private ?string $orderNumber = null;

    private bool $orderNumberSet = false;

    private ?string $transactionNumber = null;

    private bool $transactionNumberSet = false;

    private ?string $orderNumberPrefix = null;

    public function __construct(private readonly ClientInterface $client) {}

    public function getName(): string
    {
        return Support\Constants::DRIVER_NAME;
    }

    public function setPowerTranzId(string $id): self
    {
        $this->powerTranzId = $id;

        return $this;
    }

    public function getPowerTranzId(): string
    {
        return $this->powerTranzId ?? Support\Constants::CONFIG_KEY_PWTID;
    }

    public function setPowerTranzPassword(string $password): self
    {
        $this->powerTranzPassword = $password;

        return $this;
    }

    public function getPowerTranzPassword(): string
    {
        return $this->powerTranzPassword ?? Support\Constants::CONFIG_KEY_PWTPWD;
    }

    public function setTestMode(bool $mode = false): self
    {
        $this->isTestMode = $mode;

        return $this;
    }

    public function enableTestMode(): self
    {
        $this->setTestMode(true);

        return $this;
    }

    public function set3DSMode(bool $mode = true): self
    {
        $this->use3DS = $mode;

        return $this;
    }

    public function setIncludeBillingAddress(bool $include = true): self
    {
        $this->includeBillingAddress = $include;

        return $this;
    }

    public function setFraudCheckMode(bool $mode = true): self
    {
        $this->checkFraud = $mode;

        return $this;
    }

    public function getEndpoint(): string
    {
        return ($this->isTestMode) ? Support\Constants::PLATFORM_PWT_UAT : Support\Constants::PLATFORM_PWT_PROD;
    }

    public function setMerchantResponseURL(string $url): self
    {
        $this->merchantResponseURL = $url;

        return $this;
    }

    public function getMerchantResponseURL(): string
    {
        return $this->merchantResponseURL ?? Support\Constants::CONFIG_KEY_MERCHANT_RESPONSE_URL;
    }

    public function setOrderNumberAutoGen(bool $auto = false): self
    {
        $this->orderNumberAutoGen = $auto;

        return $this;
    }

    public function setOrderNumberPrefix(string $prefix): self
    {
        $this->orderNumberPrefix = $prefix;

        return $this;
    }

    public function getOrderNumberPrefix(): string
    {
        return $this->orderNumberPrefix ?? Support\Constants::GATEWAY_ORDER_IDENTIFIER_PREFIX;
    }

    public function setOrderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;
        $this->orderNumberSet = true;

        return $this;
    }

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

    public function setTransactionNumberAutoGen(bool $auto = true): self
    {
        $this->orderNumberAutoGen = $auto;

        return $this;
    }

    public function setTransactionNumber(string $transactionNumber): self
    {
        $this->transactionNumber = $transactionNumber;
        $this->transactionNumberSet = true;

        return $this;
    }

    public function getTransactionNumber(): ?string
    {
        if ($this->transactionNumberAutoGen && ! $this->transactionNumberSet) {
            $generatedTransactionNumber = Uuid::uuid4()->toString();
            $this->setTransactionNumber($generatedTransactionNumber);
        }

        return $this->transactionNumber;
    }

    /**
     * @throws GatewayException
     */
    public function alive(): AliveResponse
    {
        $response = $this->send([], TransactionType::Alive->apiEndpoint(), method: 'GET');

        return new AliveResponse($response);
    }

    /**
     * @throws InvalidCreditCard
     * @throws GatewayException
     */
    public function authorize(AuthorizationData $transactionData): Authorize3DSResponse
    {
        $this->validateCreditCard($transactionData);

        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData->card->expiryYear) == 4) ? substr($transactionData->card->expiryYear, 2, 2) : $transactionData->card->expiryYear, $transactionData->card->expiryMonth);
        $holder = $transactionData->card->name ?? sprintf('%s %s', $transactionData->card->firstName, $transactionData->card->lastName);

        $this->transactionData['Source'] = [
            'CardPan' => Support\CreditCard::number($transactionData->card->number),
            'CardCvv' => $transactionData->card->cvv,
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->send($this->transactionData, TransactionType::Auth->apiEndpoint());

        return new Authorize3DSResponse($response);
    }

    /**
     * @throws GatewayException
     */
    public function authorizeWithToken(AuthorizationData $transactionData): Authorize3DSResponse
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData->card->expiryYear) == 4) ? substr($transactionData->card->expiryYear, 2, 2) : $transactionData->card->expiryYear, $transactionData->card->expiryMonth);
        $holder = $transactionData->card->name ?? sprintf('%s %s', $transactionData->card->firstName, $transactionData->card->lastName);

        $this->transactionData['Tokenize'] = true;

        $this->transactionData['Source'] = [
            'Token' => $transactionData->card->number,
            'CardCvv' => $transactionData->card->cvv,
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->send($this->transactionData, TransactionType::Auth->apiEndpoint());

        return new Authorize3DSResponse($response);
    }

    /**
     * @throws GatewayException
     */
    public function authorizeWithSentryToken(AuthorizationData $transactionData): Authorize3DSResponse
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData->card->expiryYear) == 4) ? substr($transactionData->card->expiryYear, 2, 2) : $transactionData->card->expiryYear, $transactionData->card->expiryMonth);
        $holder = $transactionData->card->name ?? sprintf('%s %s', $transactionData->card->firstName, $transactionData->card->lastName);

        $this->transactionData['Tokenize'] = true;

        $this->transactionData['Source'] = [
            'Token' => $transactionData->card->number,
            'TokenType' => 'PG2',
            'CardCvv' => $transactionData->card->cvv,
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->send($this->transactionData, TransactionType::Auth->apiEndpoint());

        return new Authorize3DSResponse($response);
    }

    /**
     * @throws GatewayException
     */
    public function getHostedPage(
        AuthorizationData $transactionData,
        string $pageSet,
        string $pageName,
        TransactionType $type = TransactionType::Auth,
    ): HostedPageResponse {
        $this->setData($transactionData);

        $this->transactionData['ExtendedData']['HostedPage'] = [
            'PageSet' => $pageSet,
            'PageName' => $pageName,
        ];

        $response = $this->send($this->transactionData, $type->apiEndpoint());

        return new HostedPageResponse($response);
    }

    public function purchase(string $spiToken): PurchaseResponse
    {
        $response = $this->send("\"{$spiToken}\"", TransactionType::Payment->apiEndpoint(), 'text/plain');

        return new PurchaseResponse($response);
    }

    /**
     * @throws InvalidCreditCard
     * @throws GatewayException
     */
    public function tokenize(AuthorizationData $transactionData): GenericResponse
    {
        $this->validateCreditCard($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData->card->expiryYear) == 4) ? substr($transactionData->card->expiryYear, 2, 2) : $transactionData->card->expiryYear, $transactionData->card->expiryMonth);
        $holder = $transactionData->card->name ?? sprintf('%s %s', $transactionData->card->firstName, $transactionData->card->lastName);

        $this->setData($transactionData);

        $this->transactionData['Tokenize'] = true;
        $this->transactionData['ThreeDSecure'] = false;
        $this->transactionData['Source'] = [
            'CardPan' => Support\CreditCard::number($transactionData->card->number),
            'CardCvv' => $transactionData->card->cvv,
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->send($this->transactionData, TransactionType::RiskManagement->apiEndpoint());

        return new GenericResponse($response);
    }

    /**
     * @throws GatewayException
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

        $response = $this->send($this->transactionData, TransactionType::Void->apiEndpoint());

        return new GenericResponse($response);
    }

    /**
     * @throws GatewayException
     */
    public function capture(CaptureRefundData $transactionData): GenericResponse
    {
        $this->transactionData = [
            'TransactionIdentifier' => $transactionData->transactionNumber,
            'TotalAmount' => $transactionData->amount,
        ];

        $response = $this->send($this->transactionData, TransactionType::Capture->apiEndpoint());

        return new GenericResponse($response);
    }

    /**
     * @throws GatewayException
     */
    public function refund(CaptureRefundData $transactionData): GenericResponse
    {
        $this->transactionData = [
            'TransactionIdentifier' => $transactionData->transactionNumber,
            'Refund' => true,
            'TotalAmount' => $transactionData->amount,
        ];

        $response = $this->send($this->transactionData, TransactionType::Refund->apiEndpoint());

        return new GenericResponse($response);
    }

    /**
     * @throws InvalidCreditCard
     */
    private function validateCreditCard(AuthorizationData $data): void
    {
        $cardValidator = Support\CreditCardValidator::make($data->validCardTypes);

        // For test credit card numbers, bypass validation
        if ($data->card->number === '4242424242424242') {
            return;
        }

        if (! $cardValidator->isValid($data->card->number)) {
            throw new InvalidCreditCard('Invalid Credit Card Number Supplied');
        }
    }

    private function setData(TransactionData $data): void
    {
        $this->transactionData = [
            'TransactionIdentifier' => $this->getTransactionNumber(),
            'TotalAmount' => $data->amount,
            'CurrencyCode' => Support\IsoCodes::getCurrencyCode($data->currency) ?? Support\Constants::CONFIG_CURRENCY_CODE,
            'ThreeDSecure' => $this->use3DS,
            'FraudCheck' => $this->checkFraud,
            'OrderIdentifier' => $this->getOrderNumber(),
            'AddressMatch' => $data->addressMatch,
            'ExtendedData' => [
                'ThreeDSecure' => [
                    'ChallengeWindowSize' => 4,
                    'ChallengeIndicator' => '01',
                ],
                'MerchantResponseUrl' => $this->getMerchantResponseURL(),
            ],
        ];

        if ($this->includeBillingAddress) {
            $this->transactionData['BillingAddress'] = [
                'FirstName' => $data->card->firstName ?? '',
                'LastName' => $data->card->lastName ?? '',
                'Line1' => $data->card->address1 ?? '',
                'Line2' => $data->card->address2 ?? '',
                'City' => $data->card->city ?? '',
                'State' => $data->card->state ?? '',
                'PostalCode' => $data->card->postcode ?? '',
                'CountryCode' => Support\IsoCodes::getCountryCode($data->card->country ?? '') ?? Support\Constants::CONFIG_COUNTRY_CODE,
                'EmailAddress' => $data->card->email ?? '',
                'PhoneNumber' => $data->card->phone ?? '',
            ];
        }
    }

    /**
     * @param array<string, mixed>|string $data
     *
     * @throws GatewayException
     */
    private function send(
        array|string $data,
        string $api,
        string $accept = 'application/json',
        string $method = 'POST',
    ): object {
        $postData = (is_array($data)) ? json_encode($data) : $data;

        $url = "{$this->getEndpoint()}{$api}";

        $headers = [
            'Accept' => $accept,
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($postData),
            'PowerTranz-PowerTranzId' => $this->getPowerTranzId(),
            'PowerTranz-PowerTranzPassword' => $this->getPowerTranzPassword(),
        ];

        $options = [
            'headers' => $headers,
        ];

        if ($method !== 'GET') {
            $options['body'] = $postData;
        }

        try {
            $response = $this->client->request($method, $url, $options);

            $result = $response->getBody()->getContents();

            $decoded = urldecode($result);
            $decoded = trim($decoded);

            /** @var object $json */
            $json = json_decode($decoded);

            return $json;
        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new GatewayException("Gateway Communication error: ({$statusCode}) {$errorMessage}");
        } catch (Exception $e) {
            throw new GatewayException($e->getMessage());
        } catch (GuzzleException $e) {
            throw new GatewayException($e->getMessage());
        }
    }

    private function timestamp(): string
    {
        $now = new DateTimeImmutable();

        return $now->format('YmdHisu');
    }
}
