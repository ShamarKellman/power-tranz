<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Responses;

class Authorize3DSResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return false;
    }

    /**
     * Check if Redirect is Required
     */
    public function isRedirect(): bool
    {
        return isset($this->transactionData->RedirectData);
    }

    /**
     * Redirect Data
     */
    public function redirect(): string
    {
        return $this->transactionData->RedirectData;
    }
}
