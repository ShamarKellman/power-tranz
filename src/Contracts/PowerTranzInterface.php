<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Contracts;

use Shamarkellman\PowerTranz\Data\AuthorizationData;
use Shamarkellman\PowerTranz\Data\CaptureRefundData;
use Shamarkellman\PowerTranz\Responses\Authorize3DSResponse;
use Shamarkellman\PowerTranz\Responses\GenericResponse;
use Shamarkellman\PowerTranz\Responses\HostedPageResponse;
use Shamarkellman\PowerTranz\Responses\PurchaseResponse;
use Shamarkellman\PowerTranz\Responses\ThreeDSResponse;

interface PowerTranzInterface
{
    public function authorize(AuthorizationData $transactionData): Authorize3DSResponse;

    public function authorizeWithToken(AuthorizationData $transactionData): Authorize3DSResponse;

    public function authorizeWithSentryToken(AuthorizationData $transactionData): Authorize3DSResponse;

    public function getHostedPage(AuthorizationData $transactionData, string $pageSet, string $pageName): HostedPageResponse;

    public function acceptNotification(array $data): ThreeDSResponse;

    public function purchase(string $spitoken): PurchaseResponse;

    public function tokenize(AuthorizationData $transactionData): GenericResponse;

    public function void(string $transactionNumber): GenericResponse;

    public function capture(CaptureRefundData $transactionData): GenericResponse;

    public function refund(CaptureRefundData $transactionData): GenericResponse;
}
