<?php

namespace Shamarkellman\PowerTranz\Responses;

class PurchaseResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return boolval($this->transactionData->Approved) == true && intval($this->transactionData->IsoResponseCode) === 0;
    }

    public function getDataArray($amount = null): array
    {
        return [
            'transactionNumber' => $this->transactionData->TransactionIdentifier,
            'amount' => $amount ?? $this->transactionData->TotalAmount,
        ];
    }
}
