<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Data;

class AuthorizationData extends TransactionData
{
    /**
     * @param list<string> $validCardTypes
     */
    public function __construct(
        float $amount,
        public ?CardData $card = null,
        ?string $currency = null,
        ?bool $addressMatch = false,
        ?string $transactionNumber = null,
        ?string $orderNumber = null,
        public array $validCardTypes = [],
    ) {
        parent::__construct($amount, $currency, $addressMatch, $transactionNumber, $orderNumber);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'card' => $this->card->toArray(),
        ]);
    }
}
