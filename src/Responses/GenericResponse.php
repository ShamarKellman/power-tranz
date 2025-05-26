<?php

namespace Shamarkellman\PowerTranz\Responses;

class GenericResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return intval($this->transactionData->IsoResponseCode) === 1;
    }
}
