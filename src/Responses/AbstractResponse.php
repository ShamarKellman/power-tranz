<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Responses;

abstract class AbstractResponse
{
    public ?object $transactionData;

    /**
     * @param  array<string, mixed>|object  $data
     */
    public function __construct(array|object $data)
    {
        $this->transactionData = is_array($data) ? (object) $data : $data;
    }

    public function getResponseCode(): string
    {
        return $this->transactionData->RiskManagement->ThreeDSecure->ResponseCode ?? '';
    }

    public function getIsoResponseCode(): string
    {
        return $this->transactionData->IsoResponseCode ?? '';
    }

    public function getMessage(): string
    {
        return $this->transactionData->ResponseMessage ?? '';
    }

    /**
     * @return list<array{code: string, message: string}>
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

    public function getData(): object
    {
        return $this->transactionData;
    }

    public function getTransactionNumber(): string
    {
        return $this->transactionData->TransactionIdentifier ?? '';
    }

    public function getOrderNumber(): string
    {
        return $this->transactionData->OrderIdentifier ?? '';
    }

    public function getSpiToken(): string
    {
        return $this->transactionData->SpiToken ?? '';
    }

    public function getAuthorizationCode(): string
    {
        return $this->transactionData->AuthorizationCode ?? '';
    }

    public function isSuccessful(): bool
    {
        return false;
    }
}
