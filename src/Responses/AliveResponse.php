<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Responses;

class AliveResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return isset($this->transactionData->Name);
    }
}
