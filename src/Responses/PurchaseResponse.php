<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Responses;

class PurchaseResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return boolval($this->transactionData->Approved) == true && intval($this->transactionData->IsoResponseCode) === 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataArray(null|string|float $amount = null): array
    {
        return (array) $this->transactionData;
    }
}
