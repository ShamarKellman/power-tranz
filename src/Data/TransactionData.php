<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Data;

class TransactionData
{
    public function __construct(
        public float $amount,
        public ?string $currency = null,
        public ?bool $addressMatch = false,
        public ?string $transactionNumber = null,
        public ?string $orderNumber = null,
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'AddressMatch' => $this->addressMatch,
            'transactionNumber' => $this->transactionNumber,
            'orderNumber' => $this->orderNumber,
        ];
    }
}
