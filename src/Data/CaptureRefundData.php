<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Data;

class CaptureRefundData extends TransactionData
{
    public function __construct(
        float $amount,
        ?string $transactionNumber,
        ?string $currency = null,
    ) {
        parent::__construct($amount, $currency, transactionNumber: $transactionNumber);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'transactionNumber' => $this->transactionNumber,
        ]);
    }
}
