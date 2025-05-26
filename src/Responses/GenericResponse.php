<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Responses;

class GenericResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return $this->transactionData->IsoResponseCode === '00';
    }
}
