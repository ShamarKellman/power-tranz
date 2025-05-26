<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Support;

class TransactionCode
{
    public const NONE = 0;

    public const AVS_CHECK = 1;

    public const AVS_CHECK_SPEC = 2;

    public const CONTAINS_3DS_AUTH = 4;

    public const SINGLE_PASS = 8;

    public const AUTH_3DS_ONLY = 64;

    public const TOKEN_REQUEST = 128;

    public const HOSTED_PAGE_AUTH_3DS = 256;

    public const FRAUD_CHECK_ONLY = 512;

    public const FRAUD_TEST = 1024;

    public const RECURRING_FUTURE = 2048;

    public const RECURRING_INITIAL = 4096;

    public const RECURRING_INITIAL_SPEC = 8192;

    /**
     * @var list<int>
     */
    protected array $codeList = [
        self::NONE,
        self::AVS_CHECK,
        self::AVS_CHECK_SPEC,
        self::CONTAINS_3DS_AUTH,
        self::SINGLE_PASS,
        self::AUTH_3DS_ONLY,
        self::TOKEN_REQUEST,
        self::HOSTED_PAGE_AUTH_3DS,
        self::FRAUD_CHECK_ONLY,
        self::FRAUD_TEST,
        self::RECURRING_FUTURE,
        self::RECURRING_INITIAL,
        self::RECURRING_INITIAL_SPEC,
    ];

    protected int $code = 0;

    protected array $userCodes = [];

    public function __construct(array $codes)
    {
        $this->appendCodes($codes);
    }

    public function getUserCodes(): array
    {
        return $this->userCodes;
    }

    public function getCode(): string
    {
        return $this->__toString();
    }

    public function addCode($code): static
    {
        return $this->appendCodes([$code]);
    }

    public function hasCode($code): bool
    {
        if (in_array($code, $this->userCodes)) {
            return true;
        }

        return false;
    }

    protected function appendCodes(array $codes): static
    {
        foreach ($codes as $code) {
            if (in_array($code, $this->codeList) && ! in_array(intval($code), $this->userCodes)) {
                $this->code += intval($code);
                $this->userCodes[] = intval($code);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->code;
    }
}
