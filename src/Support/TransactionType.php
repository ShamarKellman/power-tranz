<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Support;

enum TransactionType: string
{
    case Alive = 'Alive';
    case Auth = 'Auth';
    case Sale = 'Sale';
    case RiskManagement = 'RiskMgmt';
    case Void = 'Void';
    case Payment = 'Payment';
    case Capture = 'Capture';
    case Refund = 'Refund';

    public function apiEndpoint(): string
    {
        return match ($this) {
            self::Alive => 'alive',
            self::Auth => 'spi/auth',
            self::Sale => 'spi/sale',
            self::RiskManagement => 'spi/riskmgmt',
            self::Payment => 'spi/payment',
            self::Void => 'void',
            self::Capture => 'capture',
            self::Refund => 'refund',
        };
    }
}
