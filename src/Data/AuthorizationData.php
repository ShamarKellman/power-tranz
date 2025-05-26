<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Data;

class AuthorizationData extends TransactionData
{
    public function __construct(
        float $amount,
        public CardData $card,
        ?string $currency = null,
        ?bool $addressMatch = false,
        ?string $transactionNumber = null,
        ?string $orderNumber = null,
    ) {
        parent::__construct($amount, $currency, $addressMatch, $transactionNumber, $orderNumber);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'card' => $this->card->toArray(),
        ]);
    }
}
