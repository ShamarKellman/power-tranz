<?php

namespace Shamarkellman\PowerTranz\Responses;

class ThreeDSResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        if (in_array($this->transactionData->IsoResponseCode, ['3D0', '3D1'])) {
            if (isset($this->transactionData->RiskManagement->ThreeDSecure->Eci)
                && in_array($this->transactionData->RiskManagement->ThreeDSecure->Eci, ['01', '02', '05', '06'])
            ) {
                return isset($this->transactionData->SpiToken);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
