<?php

namespace Shamarkellman\PowerTranz\Contracts;

use Shamarkellman\PowerTranz\Responses\Authorize3DSResponse;
use Shamarkellman\PowerTranz\Responses\GenericResponse;
use Shamarkellman\PowerTranz\Responses\HostedPageResponse;
use Shamarkellman\PowerTranz\Responses\PurchaseResponse;
use Shamarkellman\PowerTranz\Responses\ThreeDSResponse;

interface PowerTranzInterface
{
    public function authorize(array $transactionData): Authorize3DSResponse;

    public function authorizeWithToken(array $transactionData): Authorize3DSResponse;

    public function authorizeWithSentryToken(array $transactionData): Authorize3DSResponse;

    public function getHostedPage(array $transactionData, string $pageSet, string $pageName): HostedPageResponse;

    public function acceptNotification(array $data): ThreeDSResponse;

    public function purchase(string $spitoken): PurchaseResponse;

    public function tokenize(array $transactionData): GenericResponse;

    public function void(string $transactionNumber): GenericResponse;

    public function capture(array $transactionData): GenericResponse;

    public function refund(array $transactionData): GenericResponse;
}
