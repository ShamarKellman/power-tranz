<?php

namespace Shamarkellman\PowerTranz\Responses;

abstract class AbstractResponse
{
    public ?object $transactionData;

    /**
     * Power Tranz Response Constructor
     *
     * @param  array|object  $data
     */
    public function __construct(array|object $data)
    {
        $this->transactionData = is_array($data) ? (object) $data : $data;
    }

    /**
     * Get Response Code
     */
    public function getResponseCode(): string
    {
        return $this->transactionData->RiskManagement->ThreeDSecure->ResponseCode ?? '';
    }

    /**
     * Get ISO Response Code
     */
    public function getIsoResponseCode(): string
    {
        return $this->transactionData->IsoResponseCode ?? '';
    }

    /**
     * Get Response Message
     */
    public function getMessage(): string
    {
        return $this->transactionData->ResponseMessage ?? '';
    }

    /**
     * Get Error Message
     *
     * @return array{code: string, message: string}
     */
    public function getErrorMessages(): array
    {
        $errors = [];
        if (isset($this->transactionData->Errors)) {
            foreach ($this->transactionData->Errors as $error) {
                $errors[] = ['code' => $error->Code, 'message' => $error->Message];
            }
        }

        return $errors;
    }

    /**
     * Get Entire Transaction Response
     */
    public function getData(): object
    {
        return $this->transactionData;
    }

    /**
     * Get Transaction Number
     */
    public function getTransactionNumber(): string
    {
        return $this->transactionData->TransactionIdentifier ?? '';
    }

    /**
     * Get Order Number
     */
    public function getOrderNumber(): string
    {
        return $this->transactionData->OrderIdentifier ?? '';
    }

    /**
     * Get SPI Token
     */
    public function getSpiToken(): string
    {
        return $this->transactionData->SpiToken ?? '';
    }

    /**
     * Get Authorization Code
     */
    public function getAuthorizationCode(): string
    {
        return $this->transactionData->AuthorizationCode ?? '';
    }

    public function isSuccessful(): bool
    {
        return false;
    }
}
